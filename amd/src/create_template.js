define(['jquery', "core/ajax"], function($) {
    return {
        init: function() {
            const formElements = $('#create_template_form input, #create_template_form select, #create_template_form textarea');
            const form = $('#create_template_form');
            const saveButton = form.find('#id_save_template_button'); // Update this to the correct ID
            const cancelButton = form.find('#id_cancel_template_button'); // Cancel button

            // Get the current URL, including query parameters
            // const currentUrlWithParams = window.location.href;

            // Iterate over each form element to add an event listener for changes.
            formElements.each(function() {
                const element = $(this);
                element.on('change', function() {
                    markTouched(element);
                });

                if (element.is(':checkbox') || element.is(':radio')) {
                    element.on('click', function() {
                        markTouched(element);
                    });
                }
            });

            /**
             * Get query parameters from the current URL.
             * @returns {Object} An object containing the query parameters.
             * @example
             * getQueryParams(); // returns {categoryid: 1, courseid: 2}
             */
            function getQueryParams() {
                const params = {};
                const urlSearchParams = new URLSearchParams(window.location.search);
                urlSearchParams.forEach((value, key) => {
                    params[key] = value;
                });
                return params;
            }

            saveButton.on('click', function() {
                let formData = {};

                // Gather only touched elements
                formElements.each(function() {
                    const element = $(this);
                    if (element.attr('data-touched') === 'true') {
                        const htmlId = element.attr('id');
                        const settingName = htmlId.replace('id_', ''); // Derive the setting name by removing 'id_'
                        formData[settingName] = element.val();
                    }
                });

                // Add template_name and any other essential values
                formData['template_name'] = form.find('input[name="template_name"]').val();

                // Append query parameters (like categoryid, courseid, etc.)
                const queryParams = getQueryParams();
                formData = {...formData, ...queryParams};

                // Submit the form data using AJAX
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        // Handle success response
                        const responseData = JSON.parse(response);
                        if (responseData.redirect) {
                            window.location.href = responseData.redirect;
                        } else if (responseData.error) {
                            console.error(responseData.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('An error occurred:', error);
                    }
                });
            });

            // Handle cancel button click.
            cancelButton.on('click', function(event) {
                event.preventDefault(); // Prevent the default action
                window.location.href = M.cfg.wwwroot + '/local/setcheck/pages/manage_templates.php';
            });

            /**
             * @param {jQuery} element The element to mark as touched.
             */
            function markTouched(element) {
                element.attr('data-touched', 'true');
            }
        }
    };
});