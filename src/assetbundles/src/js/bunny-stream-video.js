import { Uppy, Dashboard, Tus, UIPlugin } from "uppy";
import Alpine from "alpinejs";

Alpine.store('bunnystream', {
    loading: false,
    currentPage: 1,
    totalPages: 1,
    totalItems: 0,
    view: 'grid',
    search: '',
    videos: [],

    init() {
        this.getVideos();
    },

    setPage(page) {
        this.currentPage = page;
        this.getVideos();
    },

    previousPage() {
        this.currentPage--;
        this.getVideos();
    },

    nextPage() {
        this.currentPage++;
        this.getVideos();
    },

    getVideos(forceRefresh = false) {
        this.videos = [];
        this.loading = true;

        let params = {
            page: this.currentPage,
            forceRefresh: forceRefresh,
        };

        Craft.sendActionRequest('POST', 'bunny-stream/videos/get-videos', {
            headers: {
                'accept': 'application/json',
            },
            params: params,
        })
            .then(response => {
                this.totalPages = response.data.totalPages;
                this.totalItems = response.data.totalItems;
                this.videos = response.data.videos;
                this.loading = false;
            })
            .catch(e => {
                console.log(e);
                this.loading = false;
            })
    },
});

Alpine.data('videos', (opts = {}) => ({
    page: opts.page || 1,
    libraryId: opts.libraryId || "",
    streamUrl: opts.streamUrl || "",

    init() {

    },

    get search() {
        return this.$store.bunnystream.search;
    },

    get videos() {
        return this.$store.bunnystream.videos;
    },

    get filteredVideos() {
        if(this.search.length) {
            return this.videos.filter(video => video.title.toLowerCase().includes(this.search.toLowerCase()));
        }

        return this.videos;
    }
}));

Alpine.data('video', (video) => ({
    video: video,

    get thumbUrl() {
        return `https://${this.streamUrl}/${video.guid}/${video.thumbnailFileName}`;
    },

    get previewUrl() {
        return `https://${this.streamUrl}/${video.guid}/preview.webp`;
    },

    get length() {
        return new Date(video.length * 1000).toISOString().substring(11, 19);
    },

    get size() {
        const bytes = this.video.storageSize;
        const units = ['byte', 'kilobyte', 'megabyte', 'gigabyte', 'terabyte'];

        const navigatorLocal = navigator.languages && navigator.languages.length >= 0 ? navigator.languages[0] : 'en-US'
        const unitIndex      = Math.max(0, Math.min(Math.floor(Math.log(bytes) / Math.log(1000)), units.length - 1));

        return Intl.NumberFormat(navigatorLocal, {
            style: 'unit',
            unit : units[unitIndex],
            unitDisplay: 'short',
            minimumFractionDigits: 1,
            maximumFractionDigits: 1,
        }).format(bytes / (1000 ** unitIndex))
    },

    get cpUrl() {
        return Craft.getCpUrl(`bunny-stream/${video.guid}`);
    },
}));

window.Alpine = Alpine;
Alpine.start();

class UppyBunnyVideo extends UIPlugin {
    constructor(uppy, opts) {
        super(uppy, opts);

        this.id = this.opts.id || 'UppyBunnyVideo';
        this.type = 'modifier';
    }

    create(file) {
        return Craft.sendActionRequest('POST', 'bunny-stream/videos/create-video', {
            headers: {
                'accept': 'application/json',
                'content-type': 'application/json',
            },
            data: {
                title: file.meta.name,
            }
        })
        .then(response => { return response.data.guid })
        .catch(e => {
            console.log(e);
        })
    }

    prepareUpload = async (fileIDs) => {
        const promises = fileIDs.map((fileID) => {
            const file = this.uppy.getFile(fileID);

            return this.create(file)
                .then((response) => {
                    this.uppy.setFileMeta(fileID, { bunnyId: response });
                })
                .catch((err) => {
                    this.uppy.log(err, 'warning');
                });
        });

        const emitPreprocessCompleteForAll = () => {
            fileIDs.forEach((fileID) => {
                const file = this.uppy.getFile(fileID);
                this.uppy.emit('preprocess-complete', file);
            });
        };

        const result_1 = await Promise.all(promises);
        return emitPreprocessCompleteForAll(result_1);
    }

    install() {
        this.uppy.addPreProcessor(this.prepareUpload);
    }

    uninstall() {
        this.uppy.removePreProcessor(this.prepareUpload);
    }
}

const uppy = new Uppy()
    .use(Dashboard, {
        inline: false,
        trigger: '#uppy',
        width: 'auto',
        proudlyDisplayPoweredByUppy: false,
        closeModalOnClickOutside: true,
        closeAfterFinish: true,
        metaFields: [{
            id: 'name',
            name: 'Name',
            placeholder: 'Name',
        }, {
            id: 'bunnyId',
            name: 'Bunny ID',
            render: ({ value }, h) => {
                return h(
                    'input',
                    {
                        type: 'text',
                        class: 'uppy-u-reset uppy-c-textInput uppy-Dashboard-FileCard-input bg-gray-300',
                        value: value,
                        placeholder: 'Bunny ID',
                        disabled: true
                    },
                    []
                );
            }
        }]
    })
    .use(UppyBunnyVideo, {})
    .use(Tus, {
        endpoint: 'https://video.bunnycdn.com/tusupload',
        retryDelays: [0, 3000, 5000, 10000, 20000, 60000, 60000],
        async onBeforeRequest(req, file) {
            const response = await generatePresignedSignature(file);

            req.setHeader('AuthorizationSignature', response.data.presigned_signature);
            req.setHeader('AuthorizationExpire', response.data.expiration_time);
            req.setHeader('VideoId', response.data.video_guid);
            req.setHeader('LibraryId', response.data.library_id);
        },
    });

uppy.on('complete', (result) => {
    if(typeof window.Alpine !== 'undefined') {
        window.Alpine.store('bunnystream').getVideos(true);
    }
})

async function generatePresignedSignature(file) {
    let guid = uppy.getFile(file.id).meta.bunnyId;

    return Craft.sendActionRequest('POST', 'bunny-stream/videos/generate-presigned-signature', {
        headers: {
            'accept': 'application/json',
            'content-type': 'application/json',
        },
        data: {
            guid: guid,
        }
    })
    .then(response => { return response })
    .catch(e => {
        console.log(e);
    })
}