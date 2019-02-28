function recharge(id) {
    bootbox.prompt({
        title: "充值",
        inputType: 'number',
        buttons: {
            cancel: {
                label: "取消",
                className: 'btn-danger',
            },
            confirm: {
                label: "确定",
                className: 'btn-info',
            }
        },
        callback: function (result) {
            if (result != null && result != '') {
                $.post("/admin/setting/users/recharge", {user_id: id, amount: result},function (result) {
                    if(result == 1){
                        window.location = '/admin/setting/users';
                    }else{
                        bootbox.alert("充值失败");
                    }
                });
            }
        },
    });
}