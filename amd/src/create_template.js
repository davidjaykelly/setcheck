define(['jquery', "core/ajax"], function($) {
    return {
        init: function() {
            const formElements = $('.mform input, .mform select, mform textarea');
            const form = $('.mform');
            const saveButton = form.find('#id_save_template_button'); // Update this to the correct ID

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

            form.on('submit', function(e) {
                e.preventDefault();
            });

            saveButton.on('click', function(e) {
                e.preventDefault();
                console.log("Custom save button clicked. Default form submission prevented.");
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
                    url: '/moodle/local/setcheck/pages/create_template.php',
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

            /**
             * @param {jQuery} element The element to mark as touched.
             */
            function markTouched(element) {
                element.attr('data-touched', 'true');
            }
        }
    };
});