$().ready(function(){

    $('.resource-action').click(function(){

        var resource = $(this).attr('data-resource');

        var ids = [$(this).attr('data-model')];

        if (ids[0] === undefined) {
            ids = $('.resource-model').map(function () {
                if (($(this).is(':checked') || $(this).attr('type') === 'hidden') && $(this).val() !== 'on')
                    return $(this).val();
            }).get();
        }

        var parameters = window.location.search;
        var all = 0;

        if ($("#selectAllMatching") !== undefined && $("#selectAllMatching").is(":checked"))
            all = 1;

        actionRequest(parameters,  resource, $(this).attr('data-action'), {__ids: ids, __all: all, __async: true});
    });

    $('#action-form').submit(function(event){

        var resource = $(this).attr('data-resource');
        var ids = [$(this).attr('data-model')];

        actionRequest('',  resource, $(this).find('select').val(), {__ids: ids, __all: false});
        event.preventDefault();
    });


    function actionRequest(parameters, resource, action, data){

        $.ajax({
            type: "POST",
            url: '/action-handler/'+resource+'/'+action+parameters,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: data,
            success: function(result){

                if (result.response === 'QUESTION'){
                    $('<p>'+result.message+'</p>').dialog({
                        title: result.action,
                        width: 500,
                        buttons: [
                            {
                                text: 'Cancel',
                                click: function(){
                                    $(this).dialog("close");
                                }
                            },
                            {
                                text: 'Run',
                                icon: 'ui-icon-play',
                                click: function(){
                                    data.force = 1;
                                    actionRequest(parameters, resource, action, data);
                                    $(this).dialog("close");
                                }
                            }
                        ]
                    });
                }

                if (result.response === 'MESSAGE'){
                    $('<p>'+result.message+'</p>').dialog({
                        title: result.action,
                        width: 300,
                        buttons: [
                            {
                                text: 'OK',
                                click: function(){
                                    $(this).dialog("close");
                                }
                            }
                        ]
                    });
                }

                if(result.response === 'FORM'){
                    if (data.__all) {
                        window.location = '/action/' + result.resource + '/' + action + parameters;
                    }else{
                        window.location = '/action/' + result.resource + '/' + action + '?ids=' + data.__ids.join(',')
                    }
                }

                if (result.message === 'RUN_NO_ASYNC'){

                }
            },
        });
    }

    $("#selectAll").click(function(){
        $('.resource-model').prop('checked', $(this).is(':checked'));
    });

    $("#selectAllMatching").click(function(){
        if ($(this).is(':checked') && !$("#selectAll").is(':checked')){
            $("#selectAll").click();
        }
    });
    $(".resource-model").click(function() {
        if (!$(this).is(':checked') && !$(this).hasClass('select-all-dummy')) {
            $('#selectAll').prop('checked', false);
            $('#selectAllMatching').prop('checked', false);
            $('.select-all-dummy').prop('checked', false);
        }
    });


    $('body').on('click', '.lookup-field .lookup-button', function(){
        var lookupField = $(this).closest('.lookup-field');
        lookupField.find('.loading-img').show();

        var resource = $(this).closest('.lookup-field').attr('data-resource');
        $.get('/'+resource+'/lookup', function(data){

            var content = $(data);

            content.on('click', '.resource-row', function(){
                lookupField.find('input').val($(this).attr('data-id'));
                lookupField.find('.related-value').html($(this).attr('data-name'));
                content.dialog('close');
            });

            content.on('click', 'a', function(){
                url = $(this).attr('href');
                if (url !== '#') {
                    $.get(url, function (data) {
                        content.html(data);
                    });
                    return false;
                }
            });
            content.on('submit', 'form', function(){
                $.get('/'+resource+'/lookup?' + $(this).serialize(), function(data){
                    content.html(data);
                });
                return false;
            });

            var dialog = $(content).dialog({
                title: 'Lookup',
                width:900,
            });


        }).always(function(){
            lookupField.find('.loading-img').hide();
        });
    });

});