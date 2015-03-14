$.multiUpload = {
    removeInput: function (target) {
        var container = $(target).closest('.imu-container');

        if (container.find('.imu-add').length !== 0) {
            var previous = container.prev();

            if (previous.hasClass('imu-container') && !previous.hasClass('imu-delete-container')) {
                container.find('.imu-add').insertAfter(previous.find('input').last());
            } else {
                return;
            }
        }

        container.remove();
    },
    addInput: function (target) {
        var form = $(target).closest('form');
        var toClone = form.find('.imu-container').last();
        var clone = toClone.clone();

        var add_input = toClone.find('.imu-add');

        if (add_input.length !== 0) {
            add_input.remove();
        }
        // Get name of input
        var originalName = clone.find('input.imu-control').attr('name');
        var changedName = originalName;

        // Change name in inputs
        clone.find('[name]').each(function () {
            var name = $(this).attr('name');
            var matches = name.match(/\[([0-9]+)\]/);

            if (matches) {
                var integer = parseInt(matches[1]) + 1;

                var newName = name.replace(matches[0], '[' + integer + ']');

                if (originalName === name) {
                    changedName = newName;
                }

                $(this).attr('name', newName);
            }
        });

        // Change control name in rules
        clone.find('[data-nette-rules]').each(function () {
            var str = $(this).attr('data-nette-rules');
            $(this).attr('data-nette-rules', str.replace(originalName, changedName));
        });

        toClone.after(clone);
    }
};