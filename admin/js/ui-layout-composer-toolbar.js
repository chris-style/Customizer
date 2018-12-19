jQuery(document).ready(function($) {

	var pluginOpts;
	if(typeof initial_opts !== 'undefined') {
		pluginOpts = initial_opts.plugin_options;
	}
	else {
		pluginOpts = {
			gridColumns: 2,
			initialActiveModule: ''
		};
	}

	var $actionDropzones = $( '[data-id="actions"] .radykal-dropzone' ),
		$moduleDropzone = $( '[data-id="modules"] .radykal-dropzone' ),
		$availableActions = $('#fpd-available-actions'),
		$availableModules = $('#fpd-available-modules'),
		$window = $(window);


	/********************************
	******** SETUP ******************
	*********************************/

	var $preview = fancyProductDesigner.$container;

	$preview.on('ready', function() {

		//setup initial actions
		var actionKeys = Object.keys(fancyProductDesigner.actions.currentActions);
		for(var i=0; i < actionKeys.length; ++i) {

			var key = actionKeys[i],
				zoneActions = fancyProductDesigner.actions.currentActions[key],
				$dz = $actionDropzones.filter('[data-zone="'+key+'"]');

			for(var j=0; j < zoneActions.length; ++j) {

				_addItemToZone($dz, zoneActions[j], 'action');

			}

		}

		//setup initial modules
		var modules = fancyProductDesigner.mainBar.currentModules;
		for(var i=0; i < modules.length; ++i) {

			_addItemToZone($moduleDropzone, modules[i], 'module');
			$availableModules.children('[data-module="'+modules[i]+'"]').remove();

		}


		//set toolbar placement
		$('[name="toolbar_placement"]')
		.children('[value="'+fancyProductDesigner.toolbar.placement+'"]').prop('selected', true);

	});


	/********************************
	******** LAYOUT TAB *************
	*********************************/

	var _setMainBar = function() {

		if(fancyProductDesigner && fancyProductDesigner.mainBar) {

			var contentWrapper = 'sidebar';
			if($preview.hasClass('fpd-topbar') && $('[name="topbar_layout"]:checked').val() === 'fpd-dynamic-dialog') {
				contentWrapper = 'draggable-dialog';
			}
			fancyProductDesigner.mainBar.setContentWrapper(contentWrapper);

		}

	};

	//main bar layout
	var availableLayouts = $('[name="layout"]').change(function() {

		fancyProductDesigner.deselectElement();

		$preview.removeClass(availableLayouts).addClass(this.value);

		$('#fpd-sidebar-tabs-position').toggleClass('radykal-hidden', this.value === 'fpd-topbar');
		$('#fpd-topbar-layout').toggleClass('radykal-hidden', this.value !== 'fpd-topbar');

		if(this.value !== 'fpd-topbar') {
			$preview.removeClass(topbarLayouts);
			$preview.addClass($('[name="sidebar_tabs_position"]:first').prop('checked', true).val());
		}
		else {
			$preview.removeClass(availableNavTypes);
			$preview.addClass($('[name="topbar_layout"]:first').prop('checked', true).val());
		}

		_setMainBar();

		$window.resize();
		$window.resize();

	}).map(getGroupValues).get().toString().replace(/,/g, ' ');

	//$('[name="layout"]').change();
	selectFormOptionByClass($('[name="layout"]'));


	//tab tab position
	var availableNavTypes = $('[name="sidebar_tabs_position"]').change(function() {

		$preview.removeClass(availableNavTypes).addClass(this.value);

	}).map(getGroupValues).get().toString().replace(/,/g, ' ');
	$('#fpd-sidebar-tabs-position').toggleClass('radykal-hidden', $preview.hasClass('fpd-topbar'));
	selectFormOptionByClass($('[name="sidebar_tabs_position"]'));

	//top bar layout
	var topbarLayouts = $('[name="topbar_layout"]').change(function() {

		$preview.removeClass(topbarLayouts).addClass(this.value);

		_setMainBar();


	}).map(getGroupValues).get().toString().replace(/,/g, ' ');
	$('#fpd-topbar-layout').toggleClass('radykal-hidden', !$preview.hasClass('fpd-topbar'));
	selectFormOptionByClass($('[name="topbar_layout"]'));

	//dimensions
	$('#stageWidth, #stageHeight').change(function() {

		fancyProductDesigner.setDimensions($('#stageWidth').val(), $('#stageHeight').val());
		//center demo shirt
		fancyProductDesigner.viewInstances[0].centerElement(true, true, fancyProductDesigner.viewInstances[0].getElementByTitle('demo-shirt'));
		fancyProductDesigner.viewInstances[1].centerElement(true, true, fancyProductDesigner.viewInstances[1].getElementByTitle('demo-shirt'));

	});

	//shadow
	var availableShadows = $('[name="shadow"]').change(function() {

		$preview.removeClass(availableShadows).addClass(this.value);

	}).children('option').map(getGroupValues).get().toString().replace(/,/g, ' ');
	selectFormOptionByClass($('[name="shadow"]'));

	//grid columns
	var availableGridColumns = $('[name="grid_columns"]').change(function() {

		removeGridColsClasses();
		$preview.addClass('fpd-grid-columns-'+this.value);
		$('.fpd-draggable-dialog').addClass('fpd-grid-columns-'+this.value);

	}).children('option').map(getGroupValues).get().toString().replace(/,/g, ' ');

	var removeGridColsClasses = function() {

		for(var i=0; i < availableGridColumns.length; ++i) {
			$preview.removeClass('fpd-grid-columns-'+availableGridColumns[i]);
			$('.fpd-draggable-dialog').removeClass('fpd-grid-columns-'+availableGridColumns[i])
		}

	};
	$('[name="grid_columns"]').children('option[value="'+pluginOpts.gridColumns+'"]').prop('selected', true);


	//initial active module
	var $initialActiveModuleSelect = $('[name="initial_active_module"]');
	for(var i=0; i < FPDMainBar.availableModules.length; ++i) {
		var module = FPDMainBar.availableModules[i];
		$initialActiveModuleSelect.append('<option value="'+module+'">'+(module.charAt(0).toUpperCase() + module.slice(1))+'</option>');
	}
	$initialActiveModuleSelect.children('[value="'+pluginOpts.initialActiveModule+'"]').prop('selected', true);


	//views selection pos
	var viewSelectionPos = $('[name="views_selection_pos"]').change(function() {

		$preview.removeClass(viewSelectionPos).addClass(this.value);

		if(this.value === 'fpd-views-outside') {

			$('.fpd-views-selection').insertAfter($preview);

		}
		else {

			$('.fpd-views-selection').appendTo($preview.find('.fpd-main-wrapper'));

		}

	}).children('option').map(getGroupValues).get().toString().replace(/,/g, ' ');
	selectFormOptionByClass($('[name="views_selection_pos"]'));



	/********************************
	******** MODULES TAB ************
	*********************************/
	var modules = FPDMainBar.availableModules;

	//PLUS
	if(typeof FancyProductDesignerPlus !== 'undefined') {
		modules = modules.concat(FancyProductDesignerPlus.availableModules);
	}

	for(var i=0; i < modules.length; ++i) {

		$availableModules.append('<span class="radykal-label" data-module="'+modules[i]+'">'+modules[i].replace(/-/g, ' ')+'</span>');

	}

    $moduleDropzone.droppable({
	    hoverClass: 'radykal-dropzone-hover',
	    accept: '#fpd-available-modules .radykal-label',
	    drop: function(evt, ui) {

		    var $this = $(this);

		    _addItemToZone($this, ui.helper.data('module'), 'module');
		    ui.draggable.remove();
		    setupModules();

	    }
	})
	.sortable({
		items: '> .radykal-label',
		scroll: false,
		placeholder: "ui-sortable-placeholder",
		update: function() {
			setupModules();
		}
	});

	$moduleDropzone.on('dblclick', '.radykal-label', function() {

		var $this = $(this);

		$this.siblings('.radykal-dropzone-placeholder').toggle($this.siblings('.radykal-label').length === 0);
		$this.appendTo($availableModules);

		_doModulesDraggable();
		setupModules();

	});

	var _doModulesDraggable = function() {

		$( ".radykal-label", $availableModules ).draggable({
			refreshPositions: true,
			cursor: "move",
			revert: "invalid"
	    });

	};

	_doModulesDraggable();

	function setupModules() {

		if(fancyProductDesigner && fancyProductDesigner.mainBar) {

			var modules = $moduleDropzone.children('.radykal-label').map(function(id, elem) {
				return $(elem).data('module');
			}).get();

			fancyProductDesigner.mainBar.setup(modules);

		}

	};


	/********************************
	******** ACTIONS TAB ************
	*********************************/
	var actions = FPDActions.availableActions;

	for(var i=0; i < actions.length; ++i) {

		var actionTooltip = '',
			actionCssClasses = 'radykal-label';

		if(actions[i] == 'info' && typeof fpd_ui_layout_composer_opts !== 'undefined') {
			actionTooltip = fpd_ui_layout_composer_opts.info_action_tooltip;
			actionCssClasses += ' fpd-admin-tooltip';
		}

		$availableActions.append('<span class="'+actionCssClasses+'" data-action="'+actions[i]+'" title="'+actionTooltip+'">'+actions[i].replace(/-/g, ' ')+'</span>');

	}

	$( ".radykal-label", $availableActions ).draggable({
		helper: 'clone',
		cursor: "move"
    });

    $actionDropzones.droppable({
	    hoverClass: 'radykal-dropzone-hover',
	    accept: '#fpd-available-actions .radykal-label',
	    drop: function(evt, ui) {

		    var $this = $(this);

		    _addItemToZone($this, ui.helper.data('action'), 'action');
		    $actionDropzones.sortable('refreshPositions');
		    setupActions();

	    }
	})
	.sortable({
		items: '> .radykal-label',
		scroll: true,
		placeholder: "ui-sortable-placeholder",
		update: function() {
			setupActions();
		}
	});

	$actionDropzones.on('dblclick', '.radykal-label', function() {

		var $this = $(this);

		$this.siblings('.radykal-dropzone-placeholder').toggle($this.siblings('.radykal-label').length === 0);
		$this.remove();

		setupActions();

	});

	$('.fpd-class-toggle-radio').change(function() {

		var $this = $(this),
			classes = $this.parents('div:first').find('[type="radio"]').map(getGroupValues).get().toString().replace(',', ' ');

		$preview.removeClass(classes).addClass(this.value);

	}).each(function(i, radio) {

		if($preview.hasClass(radio.value)) {
			$(radio).prop('checked', true);
		}

	});

	function setupActions() {

		if(fancyProductDesigner && fancyProductDesigner.actions) {

			var actionsObj = {};
			$actionDropzones.each(function(i, dz) {

				$dz = $(dz);

				var actions = $dz.children('.radykal-label').map(function(id, elem) {
					return $(elem).data('action');
				}).get();

				actionsObj[$dz.data('zone')] = actions;

			});

			fancyProductDesigner.actions.setup(actionsObj);

		}

	};

	/********************************
	******** TOOLBAR TAB ************
	*********************************/

	//placement
	var availableTBPlacements = $('[name="toolbar_placement"]').change(function() {

		var $uiElements = fancyProductDesigner.translatedUI,
			$body = $('body');

		if(this.value == 'smart') {

			if($body.find('.fpd-element-toolbar-smart').length == 0) {
				smartToolbarInstance = new FPDToolbarSmart($uiElements.children('.fpd-element-toolbar-smart'), fancyProductDesigner);
			}
			else {
				$body.find('.fpd-element-toolbar-smart').show();
				$body.find('.fpd-element-toolbar').hide();
			}

			fancyProductDesigner.toolbar = $body.find('.fpd-element-toolbar-smart').data('instance');

		}
		else {

			if($body.find('.fpd-element-toolbar').length == 0) {
				defaultToolbarInstance = new FPDToolbar($uiElements.children('.fpd-element-toolbar'), fancyProductDesigner);
			}
			else {
				$body.find('.fpd-element-toolbar-smart').hide();
				$body.find('.fpd-element-toolbar').show();
			}
			fancyProductDesigner.toolbar = $body.find('.fpd-element-toolbar').data('instance');

			fancyProductDesigner.toolbar.setPlacement(this.value);

		}

		var activeObj = fancyProductDesigner.currentViewInstance.getElementByTitle('demo-shirt');
		if(activeObj) {
			fancyProductDesigner.currentViewInstance.stage.setActiveObject(activeObj);
		}

	}).children('option').map(getGroupValues).get().toString().replace(/,/g, ' ');

	var $excludeToolsSelect = $('[name="toolbar_exclude_tools[]"]'),
		allTBTools = ['fill', 'move', 'reset', 'font-family', 'text-size', 'text-line-height', 'text-bold', 'text-italic', 'text-underline', 'text-align', 'text-stroke', 'text-shadow', 'curved-text', 'edit-text', 'text-letter-spacing', 'text-transform', 'fill-opacity'];

	for(var i=0; i < allTBTools.length; ++i) {

		var TBtool = allTBTools[i],
			selected = '';

		if(typeof initial_opts !== 'undefined' && initial_opts.toolbar_exclude_tools && initial_opts.toolbar_exclude_tools.indexOf(TBtool) !== -1) {
			selected = 'selected="selected"';
		}

		$excludeToolsSelect.append('<option value="'+TBtool+'" '+selected+'>'+TBtool.toUpperCase().replace(/-/g, ' ')+'</option>');

	}
	$excludeToolsSelect.change();



	/********************************
	******** COLORS TAB *************
	*********************************/

	var $previewStyle = $('#fpd-preview-styles'),
		$colorsPanel = $('.radykal-tabs-content [data-id="colors"]'),
		$updatePreviewBtn = $('#fpd-update-preview');

	$('.fpd-color-picker').wpColorPicker({
		change: function() {

			var $this = $(this);
			if($this.attr('name') == 'primary_color' || $this.attr('name') == 'secondary_color') {

				$updatePreviewBtn.removeClass('radykal-disabled');

			}

		}
	});

	$updatePreviewBtn.click(function(evt) {

		evt.preventDefault();

		$colorsPanel.find('.fpd-ui-blocker').show();

		$.ajax({
			url: fpd_admin_opts.adminAjaxUrl,
			data: {
				action: $('body').hasClass('page') ? 'fpd_demogetcss' : 'fpd_getcss',
				_ajax_nonce: fpd_admin_opts.ajaxNonce,
				primary_color: $('[name="primary_color"]').val(),
				secondary_color: $('[name="secondary_color"]').val()
			},
			type: 'post',
			dataType: 'json',
			success: function(data) {

				if(data.error) {
					radykalAlert({msg: data.error });
				}
				else {
					$previewStyle.text(data.css);
				}

				$updatePreviewBtn.addClass('radykal-disabled');
				$colorsPanel.find('.fpd-ui-blocker').hide();

			}
		});

	});



	/********************************
	******** CUSTOM CSS TAB *************
	*********************************/

	var $previewCustomCss = $('#fpd-preview-custom-css');

	$('#fpd-update-preview-custom-css').click(function() {

		var editor = ace.edit(document.getElementById('fpd-custom-css'));
		$previewCustomCss.text(editor.getValue());

	}).click();


	/********************************
	******** GUIDED TOUR TAB *************
	*********************************/

	var $gtSteps = $('.fpd-gt-steps');

	$gtSteps.sortable({
		items: '> div',
		handle: '.fpd-drag',
		placeholder: 'fpd-sortable-placeholder'
	});

	$('#fpd-add-gt-step').click(function() {
		addGtStep();
	});

	$('#fpd-test-gt').click(function() {

		var gtSettings = fpd_get_gt_step_json($gtSteps.filter(':visible'));
		fancyProductDesigner.mainOptions.guidedTour = gtSettings;

		if(fancyProductDesigner.mainOptions.guidedTour && Object.keys(fancyProductDesigner.mainOptions.guidedTour).length > 0) {
			var firstKey = Object.keys(fancyProductDesigner.mainOptions.guidedTour)[0];
			fancyProductDesigner.selectGuidedTourStep(firstKey);
		}

	});

	$gtSteps.on('click', '.fpd-gt-item-remove', function() {

		$(this).parents('.fpd-gt-item:first').remove();

	});

	$gtSteps.on('click', '.fpd-gt-css-selector', function() {

		$(this).parents('.fpd-gt-item:first').toggleClass('fpd-css-selector');

	});

	function addGtStep(targetVal, textVal, $gtStep) {

		//only if target value is passed and contains colon to prevent wpml object is showing up without wpml activated
		if(targetVal && targetVal.length < 1) {
			return;
		}

		$gtStep = $gtStep === undefined ? $gtSteps.filter('.current') : $gtStep;

		var cssSelectorClass = '',
			cssSelectorInputChecked = '';

		if(targetVal && targetVal.search(':') === -1) {
			cssSelectorClass = ' fpd-css-selector';
			cssSelectorInputChecked = ' checked="checked"';
		}

		var $lastItem = $gtStep.append('<div class="fpd-gt-item fpd-clearfix '+cssSelectorClass+'"><label class="fpd-admin-tooltip" title="Use a HTML element as target for the step."><input type="checkbox" value="1" class="fpd-gt-css-selector" '+cssSelectorInputChecked+'> CSS Selector</label><div class="fpd-gt-item-actions"><span class="fpd-drag dashicons dashicons-move"></span><span class="fpd-gt-item-remove fpd-admin-icon-bin"></span></div><select><option></option></select><input type="text" placeholder="Enter a CSS selector, e.g. #my-element"><textarea placeholder="Enter explanation text - HTML tags are supported."></textarea></div>').children('.fpd-gt-item:last');

		var targetElements = modules.concat(FPDActions.availableActions);

		for(var i=0; i < modules.length; ++i) {

			$lastItem.children('select')
			.append('<option value="module:'+modules[i]+'">Module:  '+get_gt_option_val(modules[i])+'</option>');

		}

		for(var i=0; i < FPDActions.availableActions.length; ++i) {

			$lastItem.children('select')
			.append('<option value="action:'+FPDActions.availableActions[i]+'">Action:  '+get_gt_option_val(FPDActions.availableActions[i])+'</option>');

		}

		$lastItem.children('select').select2({width: '100%', placeholder: "Select Target"});

		if(targetVal) {

			if(cssSelectorInputChecked.length > 0) {
				$lastItem.children('input[type="text"]').val(targetVal);
			}
			else {
				$lastItem.children('select').val(targetVal).trigger('change');
			}

		}

		if(textVal) {
			$lastItem.children('textarea').val(textVal);
		}

		if(typeof fpdUpdateTooltip !== 'undefined') {
			fpdUpdateTooltip();
		}

	};

	function get_gt_option_val(value) {
		return value.replace(/-/g, ' ').replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
	};

	//set initial guided tour settings
	if(typeof initial_opts !== 'undefined' && initial_opts.guided_tour) {

		if($('#fpd-gt-tab .radykal-tabs').length > 0) { //wpml active

			$gtSteps.each(function(i, step) {

				var $step = $(step),
					langCode = $step.data('id');

				if(initial_opts.guided_tour[langCode]) {

					var langGTJson = initial_opts.guided_tour[langCode];

					Object.keys(langGTJson).forEach(function(key) {
						addGtStep(key, langGTJson[key], $step);
					});

				}

			});

		}
		else {

			Object.keys(initial_opts.guided_tour).forEach(function(key) {
				addGtStep(key, initial_opts.guided_tour[key]);
			});

		}

	}


	/********************************
	******** COMMON *****************
	*********************************/

	function selectFormOptionByClass($options) {

		if($options.is('select')) {

			$options.children().each(function(i, option) {
				if($preview.hasClass(option.value)) {
					$(option).prop('selected', true);
				}
			});

		}
		else {

			$options.each(function(i, option) {
				if($preview.hasClass(option.value)) {
					$(option).prop('checked', true);
				}
			});

		}

	};

	function getGroupValues(id, elem) {
		return $(elem).val();
	};

	function _addItemToZone($zone, value, type) {

		$zone.append('<span class="radykal-label" data-'+type+'="'+value+'">'+value.replace(/-/g, ' ')+'</span>');
		$zone.children('.radykal-dropzone-placeholder').toggle($zone.children('.radykal-label').length === 0);

	};

});

function fpd_get_gt_step_json($stepContainer) {

	var settingsObj = {};

	//loop throuh all items
	$stepContainer.children('div').each(function(i, step) {

		var $step = jQuery(step),
			key = $step.hasClass('fpd-css-selector') ? $step.children('input[type="text"]').val() : $step.children('select').val();

		if(key.length > 1) {
			settingsObj[key] = $step.children('textarea').val();
		}

	});

	return settingsObj;

};