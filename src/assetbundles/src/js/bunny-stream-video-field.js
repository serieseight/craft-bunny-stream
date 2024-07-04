document.addEventListener("DOMContentLoaded",function(){
    const $field = document.querySelector(".bunny-video-field");

    if($field) {
        const $select = $field.querySelector("select")
        const $input = $field.closest(".input")
        const $preview = $input.querySelector(".bunny-video-preview");

        if($preview && typeof $select.selectize !== 'undefined') {
            Craft.sendActionRequest('GET', 'bunny-stream/videos/get-preview', {
                headers: {
                    'accept': 'application/json',
                    'content-type': 'application/json',
                },
                params: {
                    videoId: $select.value,
                }
            })
            .then(response => {
                $preview.innerHTML = response.data.html;
            })
            .catch(e => {
                console.log(e);
            })

            $select.selectize.on('change', value => {
                Craft.sendActionRequest('GET', 'bunny-stream/videos/get-preview', {
                    headers: {
                        'accept': 'application/json',
                        'content-type': 'application/json',
                    },
                    params: {
                        videoId: value,
                    }
                })
                .then(response => {
                    $preview.innerHTML = response.data.html;
                })
                .catch(e => {
                    console.log(e);
                })
            })
        }
    }
});