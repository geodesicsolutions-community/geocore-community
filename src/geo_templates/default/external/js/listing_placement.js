/*
 * JS for listing detail collection page.
 *
 * NOTE: This has been "partially" converted to use jQuery.
 *
 * 17.01.0-36-gf8f52e9
 */

jQuery(function () {
    geoListing.init();
});

var geoListing = {

    inAdmin : false,

    combinedDefaultSerial : '',

    _onComplete : [],
    _onStart : [],

    _loading : false,
    _loadingQueue : false,

    _loadQueue : [],

    _adminId : 0,
    _userId : 0,

    onComplete : function (callback) {
        if (typeof callback !== 'function') {
            jQuery.error('Invalid callback specified, not a function.');
            return;
        }
        geoListing._onComplete[geoListing._onComplete.length] = callback;
    },

    onStart : function (callback) {
        if (typeof callback !== 'function') {
            jQuery.error('Invalid callback specified, not a function.');
            return;
        }
        geoListing._onStart[geoListing._onStart.length] = callback;
    },

    init : function () {
        //start up the tag autofill
        geoListing.initTagAutofill();
        //watch the auction type and buy now only fields for changes
        jQuery('#buy_now_only').click(function () {
            geoListing.auctionTypeChange(true); });
        jQuery('#auction_type').change(function () {
            geoListing.auctionTypeChange(true); });

        //make sure everything is shown/hidden correctly
        geoListing.auctionTypeChange(false);

        if (jQuery('.combined_update_fields').length && jQuery('#combined_form').length) {
            //Now for combined steps...
            //first, store the default serialized form...
            geoListing.combinedDefaultSerial = jQuery('#combined_form').serialize();

            //now watch any selects for changes to the value
            jQuery('.combined_update_fields select')
                .unbind('.combined')
                .on('change.combined', function () {
                    geoListing.combinedUpdate(jQuery(this).closest('.combined_step_section').attr('id'));
                });
            jQuery('.combined_update_fields input[type=radio]')
                .unbind('.combined')
                .on('click.combined',function () {
                    geoListing.combinedUpdate(jQuery(this).closest('.combined_step_section').attr('id'));
                });
        }
        //make instruction buttons work
        jQuery('.show_instructions_button').click(function (e) {
            e.preventDefault();
            jQuery('#' + jQuery(this).attr('id') + '_box').toggle('fast');
        }).each(function () {
            jQuery('#' + jQuery(this).attr('id') + '_box').hide();
        });

        //stuff for auction end time
        if (jQuery('#endModeSelect').length) {
            var endModeClick = function () {
                if (jQuery('#endModeSelect').val() == '1') {
                    jQuery('#end_time').show('fast');
                    jQuery('#classified_length').hide('fast');
                } else {
                    jQuery('#end_time').hide('fast');
                    jQuery('#classified_length').show('fast');
                }
            };
            jQuery('#endModeSelect').change(endModeClick);
            endModeClick();
        }

        //watch the description field for length changes
        jQuery('#main_description').keypress(geoListing.checkLength).keyup(geoListing.getLength);
        geoListing.getLength(null); //initialize count to current length
    },

    popQueue : function () {
        //calling this method is how to say "loading is complete, so do next load in the queue".
        //console.log('poping queue');
        geoListing._loading = false;
        if (!geoListing._loadQueue.length) {
            //nothing on the queue
            return false;
        }
        //get the oldest one off of the array
        var section_changed_id = geoListing._loadQueue.shift();
        geoListing._loadingQueue = true;
        geoListing.combinedUpdate(section_changed_id);
        geoListing._loadingQueue = false;
    },

    combinedUpdate : function (section_changed_id) {
        if (geoListing._loading) {
            //already loading in progress!  Queue it up...
            //console.log('queueing up a change...');
            geoListing._loadQueue[geoListing._loadQueue.length] = section_changed_id;
            return;
        }
        //console.log('updating combined results');
        geoListing._loading = true;

        var combinedForm = jQuery('#combined_form');

        if (typeof gjWysiwyg !== 'undefined') {
            //close any wysiwyg editors...  Need to unload tiny for serialize to
            //work properly
            gjWysiwyg.removeTiny();
        }

        var formData = combinedForm.serialize();

        if (formData == geoListing.combinedDefaultSerial && !geoListing._loadingQueue) {
            //no changes to the form, nothing to update
            return geoListing.popQueue();
        }
        if (section_changed_id) {
            //see if that section currently has errors, if it does not have any
            //errors then we set it in the form URL so it does not get updated
            if (jQuery('#' + section_changed_id).find('.field_error_row').length == 0) {
                //no errors in the section, so do not need to update the contents
                formData = formData + '&ajax_section_changed=' + section_changed_id;
            }
        }

        if (gjUtil.imageUpload._pl) {
            //let it clean up after itself so it can be re-loaded...
            gjUtil.imageUpload._pl.destroy();
        }

        //Trigger any "onstart" actions
        jQuery.each(geoListing._onStart, function () {
            this();});

        //Add overlay / loading graphic
        jQuery('.combined_loading_overlay').each(function () {
            jQuery(this).width(jQuery(this).closest('.combined_step_section').width())
                .height(jQuery(this).closest('.combined_step_section').height())
                .fadeTo('fast',0.5);
            if (jQuery(this).closest('.combined_step_section').prop('id') == section_changed_id) {
                jQuery(this).find('img').show();
            } else {
                jQuery(this).find('img').hide();
            }
        });

        jQuery.post(combinedForm.attr('action'), formData, 'json').done(function (data) {
            if (data.sections) {
                //insert data into each section
                jQuery.each(data.sections, function (section_name, section_contents) {
                    if (section_name) {
                        var sectionBox = jQuery('#combined_' + section_name + '.combined_step_section');
                        sectionBox.html(section_contents);
                        gjUtil.leveledFields.init(sectionBox);
                    }
                });
                geoListing.init();
                if (typeof gjWysiwyg !== 'undefined') {
                    //close any wysiwyg editors...
                    gjWysiwyg.restoreTiny();
                }
                gjUtil.initDatePicker();
                gjUtil.lightbox.initClick();
                jQuery.each(geoListing._onComplete, function () {
                    this();});
            }
            geoListing.combinedDefaultSerial = jQuery('#combined_form').serialize();
            jQuery('.combined_loading_overlay').hide();
            geoListing.popQueue();
        });
    },

    checkLength : function (e) {
        var selection = '';
        var cur_len;
        var keynum;
        var target = jQuery('#main_description');
        if (!target.length) {
            //could not find element on page
            return;
        }
        if (window.event) { // IE
            keynum = e.keyCode
            selection = (document.selection) ? document.selection.createRange().text : ''; // check for selection
        } else if (e.which) { // Netscape/Firefox/Opera
            keynum = e.which
            selection = target.val().substring(target.selectionStart,target.selectionEnd); // check for selection
        }
        e.modifiers
        cur_len = target.val().length;

        if ( keynum != '8' && keynum != undefined && selection == '' ) { // 8 == backspace
            if ( cur_len == max_length ) {
                return false;
            } else if ( cur_len > max_length ) {
                target.val(e.target.val().substr(0,max_length));
                return false;
            }
            return true;
        }
        return true;
    },

    getLength : function (e) {
        var target = jQuery('#main_description');
        if (!target.length) {
            //could not find element on page
            return;
        }
        var char_remain = jQuery('#chars_remaining');
        if (!char_remain.length) {
            //could not find text to update
            return;
        }
        var cur_len = (target.val()).length;

        if ( cur_len > max_length ) { // double check they didnt paste something huge into the textarea
            target.val(target.val().substr(0,max_length));
            char_remain.text('0');
            return false;
        }
        char_remain.text('' + (max_length - cur_len));
        return true;
    },

    auctionTypeChange : function (activeClick) {
        var auction_type_value = jQuery('#auction_type').val();

        var is_standard = (auction_type_value == '1');
        var is_dutch = (auction_type_value == '2');
        var is_reverse = (auction_type_value == '3');

        var buy_now = jQuery('#buy_now_only');

        var is_bno = (is_standard && ((buy_now.attr('type') == 'checkbox' && buy_now.prop('checked'))
                || (buy_now.attr('type') == 'hidden' && buy_now.val() == 1)));

        //go through each thing that needs to be shown/hidden, and figure out
        //if it should show/hide based on stuff above...

        if (is_bno) {
            //hide min row and reserve row
            jQuery('#min_row,#res_row').hide('fast');
            //set values for min and reserve to blank
            jQuery('#minimum').val('');
            jQuery('#reserve').val('');

            //show the applies box
            jQuery('#price_applies_box').show('fast');
            jQuery('#price_applies').show();
            if (activeClick) {
                //only change the value in response to an actual user click
                //(i.e. NOT when simply loading the page for the first time -- that is handled by template HTML)
                jQuery('#price_applies').val('item');
            }
            jQuery('#price_applies_no_bno').hide();
        } else {
            //show min and reserve row
            jQuery('#min_row,#res_row').show('fast');

            if (is_dutch) {
                jQuery('#price_applies_box').hide('fast');
            } else {
                jQuery('#price_applies_box').show('fast');
            }
            jQuery('#price_applies').hide();
            if (activeClick) {
                //only change the value in response to an actual user click
                //(i.e. NOT when simply loading the page for the first time -- that is handled by template HTML)
                jQuery('#price_applies').val('lot');
            }
            jQuery('#price_applies_no_bno').show();
        }
        if (is_reverse) {
            jQuery('#maximum_label').show();
            jQuery('#minimum_label').hide();
        } else {
            jQuery('#maximum_label').hide();
            jQuery('#minimum_label').show();
        }

        if (is_dutch || (is_reverse && !jQuery('#buy_now_row').hasClass('reverse_buy_now'))) {
            //if dutch, or if reverse but no fancy class on container, hide buy now row
            jQuery('#buy_now_row').hide('fast');
        } else {
            jQuery('#buy_now_row').show('fast');
        }

        if (!is_standard) {
            jQuery('#buy_now_only_row').hide();
            if (jQuery('#buy_now_only').attr('type') == 'checkbox') {
                jQuery('#buy_now_only').prop('checked',false);
            }
        } else {
            jQuery('#buy_now_only_row').show();
        }
    },

    initTagAutofill : function () {
        if (!jQuery('#listingTags').length) {
            //no input found for listing tags
            return;
        }
        var pre = (geoListing.inAdmin) ? '../' : '';
        jQuery('#listingTags').autocomplete({
            source : function (request, response) {
                jQuery.getJSON(pre + 'AJAX.php?controller=ListingTagAutocomplete&action=getSuggestions', {
                    tags : request.term
                }, response);
            }
        });
    },

    costOptions : {
        _delGroupId : null,
        _msgs : {},
        _limits : {
            label_length : 0,
            max_groups : 0,
            max_options_per_group : 0
        },

        init : function () {
            //Cost options
            //NOTE: Possibly called multiple times, each time the cost option box
            //contents are changed
            jQuery('#add_buyer_option_button').unbind().click(geoListing.costOptions.addDialog);

            jQuery('#cost-options-set-combined-quantity').unbind().click(geoListing.costOptions.setCombinedQuantityDialog);

            jQuery('.cost_options_del_group').unbind().click(function (e) {
                e.preventDefault();
                geoListing.costOptions._delGroupId = jQuery(this).prop('hash').replace('#','');
                jQuery('#dialog-confirm-cost-options-delete').dialog('open');
            });


            jQuery('.cost_options_edit_group').unbind().click(geoListing.costOptions.editGroupClick);

            if (gjUtil.updateCurrencies) {
                //make sure precurrency is set
                gjUtil.updateCurrencies();
            }

            //show/hide options and buttons according to how many groups there are
            var optionGroupCount = jQuery('.cost-option-box').length;

            if (optionGroupCount >= geoListing.costOptions._limits.max_groups) {
                //hide the add button
                jQuery('#add_buyer_option_button').hide();
            } else {
                jQuery('#add_buyer_option_button').show();
            }

            if (optionGroupCount > 1) {
                //If there is at least 2 options...

                if (jQuery('.cost-option-quantity-combined').length > 1 && jQuery('.cost-option-box .error_message').length == 0) {
                    //show the button to set quantities
                    jQuery('#cost-options-set-combined-quantity').show('fast');
                } else {
                    jQuery('#cost-options-set-combined-quantity').hide();
                }

                //Add the sortable to make things able to change order
                jQuery('.cost-options-box-sortbox').sortable({
                    handle : '.cost-option-group-label',
                    update : function (event, ui) {
                        var params = jQuery(this).sortable('serialize');

                        jQuery.ajax({
                            url:'AJAX.php?controller=CostOptions&action=sortGroups&adminId=' + geoListing._adminId + '&userId=' + geoListing._userId,
                            dataType : 'json',
                            type: 'POST',
                            data: params
                        }).done(geoListing.costOptions.handleResponse)
                        .error(function () {
                            //some error sorting...  TODO: text
                            gjUtil.addError('Server Error');
                        });
                    }
                });
            } else {
                //Only 1 or fewer option...

                //hide the button to set quantities
                jQuery('#cost-options-set-combined-quantity').hide();
            }
        },

        addDialog : function (e) {
            if (e) {
                e.preventDefault();
            }
            geoListing.costOptions.promptCombineReset(function () {
                jQuery.ajax({
                    url:'AJAX.php?controller=CostOptions&action=add&adminId=' + geoListing._adminId + '&userId=' + geoListing._userId,
                    dataType : 'json'
                }).done(geoListing.costOptions.handleResponse)
                .error(function () {
                    //some error deleting...  TODO: text
                    gjUtil.addError('Server Error');
                });
            });
        },

        addOption : function (e) {
            e.preventDefault();

            jQuery('.cost_options_edit_tbody').append(jQuery('.cost_options_new_row')
                    .clone()
                    .removeClass('cost_options_new_row')
                    .addClass('cost_options_row')
                    .show('fast'));
            //make sure it watches any new remove buttons
            jQuery('.cost_options_remove_option').unbind().click(geoListing.costOptions.remOption);
            jQuery('.cost_options_edit_tbody').sortable('refresh');
            if (jQuery('.cost_options_row').length >= geoListing.costOptions._limits.max_options_per_group) {
                //reached max number of options, hide the button
                jQuery('.cost_option_add_option').hide();
            }
        },

        remOption : function (e) {
            e.preventDefault();

            jQuery(this).closest('tr').remove();
            if (jQuery('.cost_options_row').length < geoListing.costOptions._limits.max_options_per_group) {
                //make sure it shows if there are spots
                jQuery('.cost_option_add_option').show();
            }
        },

        deleteGroupClick : function () {
            var groupId = geoListing.costOptions._delGroupId;
            if (geoListing.costOptions._delGroupId === null) {
                //not set...
                return;
            }
            geoListing.costOptions.promptCombineReset(function () {
                jQuery.ajax({
                    url : 'AJAX.php?controller=CostOptions&action=deleteGroup&adminId=' + geoListing._adminId + '&userId=' + geoListing._userId,
                    data : 'groupId=' + groupId,
                    dataType : 'json',
                    type : 'POST'
                }).done(geoListing.costOptions.handleResponse);
                geoListing.costOptions._delGroupId = null;
            });
        },

        editGroupClick : function (e) {
            e.preventDefault();

            var groupId = jQuery(this).prop('hash').replace('#','');

            geoListing.costOptions.promptCombineReset(function () {
                jQuery.ajax({
                    url : 'AJAX.php?controller=CostOptions&action=editGroup&adminId=' + geoListing._adminId + '&userId=' + geoListing._userId,
                    data : 'groupId=' + groupId,
                    dataType : 'json',
                    type : 'POST'
                }).done(geoListing.costOptions.handleResponse);
            });
        },

        editSubmit : function (e) {
            e.preventDefault();

            var data = jQuery(this).serialize();

            jQuery.ajax({
                url : 'AJAX.php?controller=CostOptions&action=update&adminId=' + geoListing._adminId + '&userId=' + geoListing._userId,
                data : data,
                dataType : 'json',
                type : 'POST'
            }).done(geoListing.costOptions.handleResponse);
        },

        setCombinedQuantityDialog : function (e) {
            e.preventDefault();

            jQuery.ajax({
                url : 'AJAX.php?controller=CostOptions&action=editCombinedQuantity&adminId=' + geoListing._adminId + '&userId=' + geoListing._userId,
                dataType : 'json',
                type : 'POST'
            }).done(geoListing.costOptions.handleResponse);
        },

        setCombinedQuantitySubmit : function (e) {
            e.preventDefault();

            var data = jQuery(this).serialize();

            jQuery.ajax({
                url : 'AJAX.php?controller=CostOptions&action=updateCombinedQuantity&adminId=' + geoListing._adminId + '&userId=' + geoListing._userId,
                data : data,
                dataType : 'json',
                type : 'POST'
            }).done(geoListing.costOptions.handleResponse);
        },

        addCombinedQuantity : function (e) {
            e.preventDefault();

            //get the "last" row, make sure the selections start out "one further"
            var prevRow = jQuery('.cost-options-combined-tbody tr:last').not('.cost-options-new-combined-row');

            var newRow = jQuery('.cost-options-new-combined-row')
                .clone()
                .removeClass('cost-options-new-combined-row');

            jQuery('.cost-options-combined-tbody').append(newRow.show());
            //watch changes on select
            newRow.find('select').change(geoListing.costOptions.quantitySelectChange);

            if (prevRow.length) {
                //first make the selects match up...
                var bumpVal = '';
                newRow.find('select').each(function () {
                    var matching = prevRow.find('option[value=' + jQuery(this).val() + ']').closest('select');
                    jQuery(this).val(matching.val());
                    bumpVal = matching.val();
                });

                //Bump value by one...
                if (bumpVal) {
                    geoListing.costOptions._rollOptions(newRow.find('option[value=' + bumpVal + ']'));
                }
            }
            //trigger a change to set the name
            newRow.find('select').filter(':first').change();

            //make sure it watches any new remove buttons
            jQuery('.cost_options_remove_option').unbind().click(geoListing.costOptions.remOption);
        },

        quantitySelectChange : function (e) {
            //need to update the "name" for the quantity.
            var name = 'cost_options_quantity[';
            jQuery(this).closest('tr').find('select').each(function () {
                name += jQuery(this).val() + '_';
            });
            //remove last _
            name = name.substring(0,name.length - 1);
            name += ']'

            jQuery(this).closest('tr').find('.cost-options-number').prop('name',name);
        },

        _rollOptions : function (option) {
            var setVal = '';
            if (option.next().length == 0) {
                //roll it to first entry...
                setVal = option.siblings(':first').val();
                if (!setVal) {
                    //cannot set value
                    return;
                }
                option.closest('select').val(setVal);
                //roll the previous selection

                var prev = option.closest('td');
                if (!prev.length) {
                    //some problem traveling up to parent td
                    return;
                }
                prev = prev.prev('td');
                if (!prev.length) {
                    //there is no previous one...
                    return;
                }
                geoListing.costOptions._rollOptions(prev.find('option[value=' + prev.find('select').val() + ']'));
            } else {
                //just roll it to next one
                setVal = option.next().val();
                if (!setVal) {
                    //cannot set value
                    return;
                }
                option.closest('select').val(setVal);
            }
        },

        handleResponse : function (response) {
            if (response.error) {
                geoListing.costOptions.handleError(response.error);
                return;
            }
            if (response.msg) {
                gjUtil.addMessage(response.msg);
            }
            if (response.dialog) {
                jQuery('#add-cost-dialog-box').html(response.dialog)
                    .attr({title : response.dialog_title || ''})
                    .dialog({
                        autoOpen : false,
                        modal: true,
                        buttons: [
                             {
                                    text: geoListing.costOptions._msgs.ok,
                                    click: function () {
                                        jQuery('.cost-options-edit-form').submit();}
                        },
                             {
                                    text: geoListing.costOptions._msgs.cancel,
                                    click: function () {
                                        jQuery(this).dialog('close'); }
                        }
                        ],
                        width: 'auto'
                    }).dialog('open');
            }
            if (response.cost_options_box) {
                //updating cost options box...

                //close the dialog if it is open
                if (jQuery('#add-cost-dialog-box').dialog('isOpen')) {
                    jQuery('#add-cost-dialog-box').dialog('close');
                }

                //and insert the new contents
                jQuery('#cost_options_box').html(response.cost_options_box);
                //re-init the buttons
                geoListing.costOptions.init();
                //just updated cost options thing, reset del group just to be safe
                geoListing.costOptions._delGroupId = null;

                if (jQuery('.cost-option-box').length >= geoListing.costOptions._limits.max_groups) {
                    //hide the add button
                    jQuery('#add_buyer_option_button').hide();
                } else {
                    jQuery('#add_buyer_option_button').show();
                }

                //remove error class / messages to avoid confusion over whether problem is fixed or not
                jQuery('#cost_options_box_outer').removeClass('field_error_row')
                    .find('.cost_options_main_error').hide();
            }
            if (response.update_quantity && jQuery('#auction_quantity').attr('type') !== 'hidden') {
                //set the quantity used
                jQuery('#auction_quantity').val(response.update_quantity);
            }
            if (response.debug) {
                console.log('Debug: ' + response.debug);
            }
        },

        checkQuantity : function () {
            if (jQuery(this).prop('checked')) {
                //dialog for whether to show combined thingy or not
                jQuery('#cost-options-combine-option-box').dialog({
                    buttons : {
                        Individual : function () {
                            jQuery('#cost-options-quantity-type').val('individual');

                            jQuery('#cost-options-quantity-individual-span').show();
                            jQuery('#cost-options-quantity-combined-span').hide();
                            jQuery('.cost-options-individual-quantity').show();

                            jQuery(this).dialog('close');
                        },
                        Combined : function () {
                            jQuery('#cost-options-quantity-type').val('combined');

                            jQuery('#cost-options-quantity-individual-span').hide();
                            jQuery('#cost-options-quantity-combined-span').show();
                            jQuery('.cost-options-individual-quantity').hide();

                            jQuery(this).dialog('close');
                        }
                    },
                    modal : true
                });
            } else {
                jQuery('#cost-options-quantity-type').val('none');

                jQuery('#cost-options-quantity-individual-span').hide();
                jQuery('#cost-options-quantity-combined-span').hide();
                jQuery('.cost-options-individual-quantity').hide();
            }
        },
        /**
         * Call for any actions that will reset the combined quantities
         */
        promptCombineReset : function (callback) {
            if (jQuery('.cost-option-combined-quantity-box').length == 0) {
                //no combined quantity, so no problem...
                callback();
                return;
            }
            jQuery('#dialog-confirm-cost-options-reset-combined').dialog({
                buttons : {
                    'Continue' : function () {
                        jQuery(this).dialog('close');
                        callback();
                    },
                    'Cancel' : function () {
                        jQuery(this).dialog('close');
                    }
                },
                modal : true
            });
        },

        handleError : function (error) {
            gjUtil.addError(error);
        }
    }
};