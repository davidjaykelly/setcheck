// File: local/setcheck/amd/src/apply_template.js
define(["jquery", "core/str", "core/ajax"], function($, Str, Ajax) {
    return {
        init: function() {
            // Click handler for the "Apply Template" button.
            $("#id_apply_setcheck_template").click(function(e) {
                e.preventDefault(); // Prevent the form submission.

                var templateId = $("#id_setcheck_template").val();
                if (templateId == 0) {
                    return; // No template selected.
                }

                // Make AJAX call to fetch template data.
                Ajax.call([
                    {
                        methodname: "local_setcheck_get_template",
                        args: {
                            templateid: templateId,
                        },
                        done: function(data) {
                            console.log(data);
                            // Populate form fields with template data.
                            for (var key in data) {
                                if (data.hasOwnProperty(key)) {
                                    var element = $("#id_" + key);
                                    if (element.length > 0) {
                                        element.val(data[key]);
                                    }
                                }
                            }

                            // Show success message to indicate that the template has been applied.
                            Str.get_string("template_applied_success", "local_setcheck").done(
                                function(s) {
                                    $("#id_setcheck_header").after(
                                        '<div class="alert alert-success">' + s + "</div>"
                                    );
                                }
                            );
                        },
                        fail: function(error) {
                            console.log(error);
                            // Show error message if there was an issue.
                            Str.get_string("template_applied_error", "local_setcheck").done(
                                function(s) {
                                    $("#id_setcheck_header").after(
                                        '<div class="alert alert-danger">' + s + "</div>"
                                    );
                                }
                            );
                        },
                    },
                ]);
            });
        },
    };
});
