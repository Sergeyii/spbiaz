var LoadOpenData = function () {
    this.init = function () {
        $('#import-from-open-data').on('click', this.load.bind(this));
    };

    this.load = function (ev) {
        $btn = $(ev.currentTarget);
        var l = $btn.ladda();
        $.ajax({
            beforeSend: function () {
                l.ladda('start');
                $btn.prop('disable', true);
            },
            url: '/open-data/default/import',
            success: function (msgCont) {
                if (msgCont.status) {
                    toastr.success(msgCont.msg);
                    _.delay(window.location.reload.bind(window.location), 10000);
                } else {
                    toastr.error(msgCont.msg);
                }
            },
            error: function (xhr, status, er) {
                toastr.error(er);
            },
            complete: function () {
                l.ladda('stop');
                $btn.prop('disable', false);
            }
        });
    };

    this.init();
    return this;
};