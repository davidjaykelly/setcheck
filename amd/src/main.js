define(['jquery'], function($) {
    return {
        init: function() {
            $('#apply-settings').on('click', function() {
                this.performAction('apply');
            }.bind(this));

            $('#check-settings').on('click', function() {
                this.performAction('check');
            }.bind(this));

            $('#amend-errors').on('click', function() {
                this.performAction('amend');
            }.bind(this));
        },

        performAction: function(action) {
            var templateId = $('#template-select').val();
            var assignmentId = $('#assignment-select').val();

            if (!templateId || !assignmentId) {
                return;
            }

            $.ajax({
                url: M.cfg.wwwroot + '/local/setcheck/ajax.php',
                type: 'POST',
                data: {
                    action: action,
                    templateid: templateId,
                    assignmentid: assignmentId
                },
                dataType: 'json',
                success: function(response) {
                    $('#result-container').html(JSON.stringify(response));
                },
                error: function() {
                    $('#result-container').html('An error occurred.');
                }
            });
        }
    };
});