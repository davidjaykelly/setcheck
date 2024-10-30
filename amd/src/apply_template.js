define(["jquery", "core/str", "core/ajax"], function($, Str, Ajax) {
    return {
        init: function() {
            // Click handler for the "Apply Template" button.
            $("#id_apply_setcheck_template").click(function(e) {
                e.preventDefault(); // Prevent the form submission.

                var templateId = $("#id_setcheck_template option:selected").val();
                if (templateId == 0) {
                    return; // No template selected.
                }

                // Make AJAX call to fetch template data.
                Ajax.call([
                    {
                        methodname: "local_setcheck_get_template",
                        args: {templateid: templateId},
                        done: function(data) {
                            console.log("Template data:", data[0].settings);
                            const settings = data[0].settings; // Assign settings to a variable
                            applyTemplateSettings(settings); // Call to apply settings

                            // Show success message to indicate the template has been applied.
                            Str.get_string("template_applied_success", "local_setcheck").done(
                                function(s) {
                                    $("#id_setcheck_header").after(
                                        '<div class="alert alert-success">' + s + "</div>"
                                    );
                                }
                            );
                        },
                        fail: function(error) {
                            console.log("Error:", error);
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

            /**
             * Apply template settings to form fields.
             * @param {Object} settings - Template settings object with field values and attributes.
             */
            function applyTemplateSettings(settings) {
                expandSections(); // Ensure all form sections are open

                settings.forEach(function(setting) {
                    var fieldId = setting.html_id;
                    var fieldValue = setting.value;
                    var element = $('#' + fieldId);

                    if (element.length > 0) {
                        // Determine the field type and apply the setting.
                        var fieldType = element.prop("type");

                        if (fieldType === "checkbox") {
                            $('#' + fieldId).prop("checked", fieldValue === "1" || fieldValue === true);
                        } else if (fieldType === "text" || fieldType === "textarea" || fieldType === "select-one") {
                            element.val(fieldValue);
                        } else if (fieldType === "radio") {
                            $('input[name="' + element.attr("name") + '"][value="' + fieldValue + '"]').prop("checked", true);
                        }
                    }
                });
            }

            /**
             * Expand all collapsible sections in the form to ensure fields are accessible.
             */
            function expandSections() {
                $(".collapsemenu").attr("aria-expanded", "true").removeClass("collapsed");
                $("fieldset.collapsible").removeClass("collapsed").addClass("show");
            }
        },
    };
});
