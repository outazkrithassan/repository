$(document).ready(function () {

    function prepareForm(form_id) {
        let send = true;

        $(`#${form_id} .form_element`).each(function () {
            let currentElement = $(this);
            $(`#error_${$(this).attr('id')}`).text('');

            currentElement.removeClass('is-invalid');
            if ((currentElement.val() == "" || currentElement.val() == undefined) && currentElement.attr('required')) {
                currentElement.addClass('is-invalid');
                send = false;
            }
        })

        return send;


    };

    function handleErrors(errors) {
        Object.keys(errors).forEach(function (key) {
            let elementId = key;
            let textContent = errors[key];

            $(`#${elementId}`).addClass('is-invalid');
            $(`#error_${elementId}`).text(textContent);
        });

    }



    $(document).on('click', '.create-btn', function () {
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        let route = $(this).attr('route');

        axios.get(route,
            {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then((resp) => {
                $('.main_modal').modal('show');
                $('.main_modal .modal-dialog').html("");
                $('.main_modal .modal-dialog').html(resp.data);
            })
            .catch((response) => {
                console.log(response);

            })
    })



    $('.main_modal').on('click', '#send_form', function () {

        let route = $(this).attr('route');
        let form_id = $(this).attr('form');
        let table = $(this).attr('table');


        if (prepareForm(form_id)) {
            let csrfToken = $('meta[name="csrf-token"]').attr('content');
            let data = new FormData($(`#${form_id}`)[0]);


            $('#send_form').prop('disabled', true);
            $('#send_form').text("Traitement ...");

            axios.post(route, data,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'X-CSRF-Token': csrfToken
                    }
                })
                .then(({ data }) => {

                    if (data.ok) {
                        $('.modal').modal('hide');
                        $(`#${table}`).DataTable().ajax.reload();
                    }

                    Toastify({
                        text: data.message,
                        className: 'bg-' + data.type,
                    }).showToast();

                })
                .catch(({ response }) => {
                    console.log(response);
                    if (response.data.errors)
                        handleErrors(response.data.errors);
                    else {
                        $('.modal').modal('hide');
                        Toastify({
                            text: 'somthing went wrong !!',
                            className: 'bg-danger',
                        }).showToast();
                    }

                }).finally(() => {
                    $('#send_form').prop('disabled', false);
                })
        }

    })


    $(document).on('click', '.edit', function () {
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        let route = $(this).attr('route');

        axios.get(route,
            {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then((resp) => {
                $('.main_modal').modal('show');
                $('.main_modal .modal-dialog').html("");
                $('.main_modal .modal-dialog').html(resp.data);
            })
            .catch((err) => {
                if (err.errors) {
                    Toastify({
                        text: 'somthing went wrong !!',
                        className: 'danger',
                    }).showToast();
                }

                console.log(err);


            })
    })


    $(document).on('click', '.delete', function () {
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        let route = $(this).attr('route');
        let table = $(this).attr('table');

        let Toast = Toastify({
            text: "Encour de suppression ...",
            className: 'bg-info',
            duration: 0
        }).showToast();

        axios.delete(route,
            {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(({ data }) => {
                if (data.ok) {
                    $(`#${table}`).DataTable().ajax.reload();
                }

                Toastify({
                    text: data.message,
                    className: 'bg-' + data.type,
                }).showToast();
            })
            .catch(({ response }) => {
                console.log(response);
                Toastify({
                    text: 'somthing went wrong !!',
                    className: 'bg-danger',
                }).showToast();
            }).finally(() => Toast.hideToast());
    })

})

function useDatatable(options) {
    return $(`#${options.id}`).DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": options.url,
            "type": "GET",
            "data": function (data) {
                if (options.data != undefined) {
                    options.data.forEach(param => {
                        data[param] = $(`#${param}`).val();
                    });
                }
            }
        },
        "columns": options.cols.map(col => ({ "data": col }))
    });
}


$('.filter_data').on('change keyup', function () {
    let table = $(this).attr('table_id');
    $(`#${table}`).DataTable().ajax.reload();
});
