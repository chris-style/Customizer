jQuery(document).ready(function($) {

	var $fpd = $('#fpd-preview-wrapper'),
		fancyProductDesigner,
		fpdActions,
		viewInstance = null,
		stage = null,
		mediaUploader = null,
		$currentListItem = null,
		changesAreSaved = true,
		boundingBoxRect = null,
		$elementLists = $('#fpd-elements-list'),
		$parametersPanel = $('#fpd-edit-parameters > .radykal-tabs'),
		$parametersForm =  $('form#fpd-elements-form'),
		updatingFormFields = false,
		productCreated = false,
		initialProduct = [],
		currentViewOptions = null,
		printingBoxRect = null,
		pluginsOptions = {
			stageWidth: Number($fpd.data('stagewidth')),
			stageHeight: Number($fpd.data('stageheight')),
			responsive: false,
	    	langJSON: false,
	    	templatesDirectory: false,
	    	editorMode: true,
	    	keyboardControl: false,
	    	deselectActiveOnOutside: false,
	    	uploadZonesTopped: parseInt(fpd_product_builder_opts.uploadZonesTopped),
	    	elementParameters: {
		    	originX: fpd_product_builder_opts.originX,
				originY: fpd_product_builder_opts.originY,
	    	},
	    	textParameters: {
		    	fontFamily: fpd_product_builder_opts.defaultFont,
				fontSize: 18
	    	},
	    	fonts: JSON.parse(fpd_product_builder_opts.enabled_fonts)

		};

	//dropdown handler for choicing a view
	$('#fpd-view-switcher').change(function() {
		var $this = $(this);

		$('#fpd-submit').attr('action', fpd_product_builder_opts.adminUrl+"admin.php?page=fpd_product_builder&view_id="+$this.val()+"").submit();

	});

	//edit product options
	var $modalEditViewOptions = $('#fpd-modal-edit-view-options');
	$('#fpd-edit-view-options').click(function(evt) {

		evt.preventDefault();

		openModal($modalEditViewOptions);
		toggleModalLoader($modalEditViewOptions, true);

		$.ajax({
			url: fpd_admin_opts.adminAjaxUrl,
			data: {
				action: 'fpd_loadviewoptions',
				_ajax_nonce: fpd_admin_opts.ajaxNonce,
				view_id: $fpd.data('viewid')
			},
			type: 'post',
			dataType: 'json',
			success: function(data) {

				toggleModalLoader($modalEditViewOptions, false);
				if(data && data.options) {

					currentViewOptions = data.options;
					if(!_.isObject(currentViewOptions)) {
						currentViewOptions = JSON.parse(currentViewOptions);
					}

					fpdFillFormWithObject(currentViewOptions, $modalEditViewOptions);

				}

			}
		});

	});

	$modalEditViewOptions.on('change', '[name="output_format"]', function() {

		$modalEditViewOptions.find('[name="output_width"], [name="output_height"]')
		.parents('tr').toggle(this.value === 'man');

		if(this.value == '') {
			$modalEditViewOptions.find('[name="output_width"]').val('')
			$modalEditViewOptions.find('[name="output_height"]').val('');
		}
		else if(this.value !== 'man') {

			var size = this.value.split('x');
			$modalEditViewOptions.find('[name="output_width"]').val(size[0])
			$modalEditViewOptions.find('[name="output_height"]').val(size[1]);

		}

	}).find('[name="output_format"]').change();

	$modalEditViewOptions.find('.fpd-save-admin-modal').click(function() {

		var $formFields = $modalEditViewOptions.find('input, select'),
			printingBoxChanged = false,
			newValues = fpdSerializeObject($formFields);

		newValues = _.isEmpty(newValues) ? '' : newValues;

		currentViewOptions = currentViewOptions === null || currentViewOptions.length === 0 ? {} : currentViewOptions;
		if(currentViewOptions.mask) {
			newValues.mask = currentViewOptions.mask;
		}

		//save old printing box
		if(currentViewOptions.printing_box) {
			newValues.printing_box = currentViewOptions.printing_box;
		}

		//check if new width and height is set for output
		if(currentViewOptions.output_width !== newValues.output_width || currentViewOptions.output_height !== newValues.output_height) {

			if(newValues.output_width !== undefined && newValues.output_height !== undefined) {

				var mmWidth = parseInt(newValues.output_width),
					mmHeight = parseInt(newValues.output_height),
					pxWidth = (72 * mmWidth) / 25.4,
					pxHeight = (72 * mmHeight) / 25.4;

				_updatePrintingBox(pxWidth, pxHeight);

			}
			else {
				_updatePrintingBox(); //remove printing box if no output width/height is set
			}

			if(printingBoxRect) { //save new printing box coords

				var bbBoundingRect = printingBoxRect.getBoundingRect();
				Object.keys(bbBoundingRect).forEach(function(key) {
					bbBoundingRect[key] = parseInt(bbBoundingRect[key]);
				});

				newValues.printing_box = bbBoundingRect;
				printingBoxChanged = true;

			}
			else { // width or height is not set, remove printing box
				newValues.printing_box = null;
			}

		}

		toggleModalLoader($modalEditViewOptions, true);

		$.ajax({
			url: fpd_admin_opts.adminAjaxUrl,
			data: {
				action: 'fpd_editview',
				_ajax_nonce: fpd_admin_opts.ajaxNonce,
				id: $fpd.data('viewid'),
				options: JSON.stringify(newValues)
			},
			type: 'post',
			dataType: 'json',
			success: function(data) {

				toggleModalLoader($modalEditViewOptions, false);
				closeModal($modalEditViewOptions);

				if(!_.isUndefined(data) && !_.isUndefined(data.columns)) {

					if(!_.isUndefined(data.columns.options)) {

						var stageWidth = fpdGlobalProductBuilderOpts.stageWidthTemp,
							stageHeight = fpdGlobalProductBuilderOpts.stageHeightTemp;

						if(newValues.hasOwnProperty('stage_width')) {
							stageWidth = parseInt(newValues.stage_width);
						}

						if(newValues.hasOwnProperty('stage_height')) {
							stageHeight = parseInt(newValues.stage_height);
						}

						$('#fpd-stage-width-label').text(stageWidth);
						$('#fpd-stage-height-label').text(stageHeight);
						fancyProductDesigner.setDimensions(stageWidth, stageHeight);

						fpdMessage(data.message, 'success');
					}
					else {
						fpdMessage(fpd_admin_opts.tryAgain, 'error');
					}

				}

			}
		});

	});

	//when select a list item, select the corresponding element in stage
	$elementLists.on('click', '.fpd-layer-item', function(evt) {
		stage.setActiveObject(viewInstance.getElementByID(this.id));
	});

	//make elements list sortable
	var sortDir = 0;
	$elementLists.sortable({
		placeholder: 'fpd-sortable-placeholder',
		helper : 'clone',
		start: function(evt, ui) {
			sortDir = ui.originalPosition.left;
		},
		change: function(evt, ui) {

			var targetElement = viewInstance.getElementByID(ui.item.attr('id')),
				relatedItem;

			if(ui.position.left > sortDir) { //down
				relatedItem = ui.placeholder.prevAll(".fpd-layer-item:not(.ui-sortable-helper)").first();
			}
			else { //up
				relatedItem = ui.placeholder.nextAll(".fpd-layer-item:not(.ui-sortable-helper)").first();
			}

			var fabricElem = viewInstance.getElementByID(relatedItem.attr('id')),
				index = viewInstance.getZIndex(fabricElem);

			targetElement.moveTo(index);

			sortDir = ui.position.left;
			changesAreSaved = false;

		}
	});

	 $(".tm-input").tagsManager({
		delimiters: [13]
	})
	.on('tm:pushed', function(e, tag) {

		var $this = $(this);

		if(tag.search(',') > -1) {
			$this.tagsManager('popTag');
			var colorsArray = tag.split(',');
			for(var i=0; i < colorsArray.length; ++i) {
				$this.tagsManager('pushTag', colorsArray[i]);
			}
		}
		else {

			if(FPDUtil.isHex(tag)) {

				if(hasUpperCase(tag)) {
					$this.tagsManager('popTag');
					$this.tagsManager('pushTag', tag.toLowerCase());
				}

				$('.tm-tag:last').css('background-color', tag);
			}
			else {
				$this.tagsManager('popTag');
			}


		}

		_setParameters();

    }).on('tm:spliced', function(e, tag) {

    	_setParameters();

    });

    //update font families in select2 dropdown
    $('select[name="fontFamily"]').on("select2:open", function(evt) {

	    setTimeout(function() {

		    $('.select2-results li').each(function(key, item) {
			    var $item = $(item);
			    $item.css('font-family', $item.text())
		    });

	    }, 1);
	});

    function hasUpperCase(str) {
	    return str.toLowerCase() != str;
	}

	//add color via btn
	$('#fpd-add-color').click(function(evt) {

		evt.preventDefault();

		var evt = jQuery.Event("keydown");
		evt.which = 13;
		$(".tm-input").trigger(evt);

	});

	//change element text when related input text field is changed
	$elementLists.on('keyup', '[name="element_sources[]"]', function(evt) {

		var $this = $(this),
			activeObj = stage.getActiveObject();

		//when list item is not selected
		if(activeObj === undefined) {
			$this.parents('li:first').click();
			activeObj = stage.getActiveObject()
		}

		if(FPDUtil.getType(activeObj.type) == 'text') {
			activeObj.set('text', this.value);
			activeObj.setCoords();
			stage.renderAll().calcOffset();
			$currentListItem.find('[name="element_titles[]"]').val(this.value);
		}

	});

	//change image source handler
	$elementLists.on('click', '.fpd-change-image', function(evt) {

		evt.preventDefault();

		var $this = $(this),
			$listItem = $this.parents('.fpd-layer-item:first'),
			element = viewInstance.getElementByID($listItem.attr('id'));

        mediaUploader = wp.media({
            title: fpd_product_builder_opts.chooseElementImageTitle,
            button: {
                text: fpd_product_builder_opts.set
            },
            multiple: false
        });

		mediaUploader.on('select', function() {

			fabric.util.loadImage(mediaUploader.state().get('selection').toJSON()[0].url, function(img) {

				$listItem.find('img').attr('src', img.src);
				$listItem.find('[name="element_sources[]"]').val(img.src);
				element.setElement(img);
				element.setCoords();
				stage.renderAll();

			});

			mediaUploader = null;

        });

        mediaUploader.open();

	});

	//element lock handler
	$elementLists.on('click', '.fpd-lock-element', function(evt) {

		evt.preventDefault();
		evt.stopPropagation();

		var $this = $(this),
			$lockInput = $('[name="adminLocked"]'),
			element = viewInstance.getElementByID($this.parents('.fpd-layer-item:first').attr('id'));

		stage.setActiveObject(element);

		//lock
		if($this.children('i').hasClass('fpd-admin-icon-lock-open')) {
			$this.children('i').removeClass('fpd-admin-icon-lock-open').addClass('fpd-admin-icon-lock');
			$lockInput.prop('checked', true).change();
			element.set('evented', false);
			fancyProductDesigner.deselectElement();
		}
		//unlock
		else {
			$this.children('i').removeClass('fpd-admin-icon-lock').addClass('fpd-admin-icon-lock-open');
			$lockInput.prop('checked', false).change();
			element.set('evented', true);
		}

		_updateFormState();
	});

	//remove element
	$elementLists.on('click', '.fpd-trash-element', function(evt) {

		evt.preventDefault();
		evt.stopPropagation();

		var $this = $(this);

		radykalConfirm({ msg: fpd_product_builder_opts.removeElement}, function(c) {

			if(c) {

				viewInstance.removeElement(viewInstance.getElementByID($this.parents('.fpd-layer-item:first').attr('id')));

			}

		});

	});

	//radio butons handler
	$('.fpd-radio-buttons').prevAll('.button').click(function(evt) {

		evt.preventDefault();

		var $this = $(this);

		$this.addClass('active').siblings('.button').removeClass('active');
		$this.nextAll('.fpd-radio-buttons').val($this.data('value')).change();

	});

	//toggle buttons handler
	$('.fpd-toggle-button').prevAll('.button').click(function(evt) {

		evt.preventDefault();

		var $this = $(this);
			$checkbox = $this.toggleClass('active').nextAll('.fpd-toggle-button').filter('[value="'+$this.data('value')+'"]');

		$checkbox .prop('checked', $this.hasClass('active')).change();

	});

	//lock/unlock prop scaling
	$('#fpd-scale-locker').click(function() {

		var $this = $(this);

		if($this.hasClass('fpd-admin-icon-lock')) {
			$this.removeClass().addClass('fpd-admin-icon-lock-open')
			.prev('label').removeClass('radykal-disabled');
		}
		else {
			$this.removeClass().addClass('fpd-admin-icon-lock')
			.prev('label').addClass('radykal-disabled');
		}

	});

	//undo/redo
	$('#fpd-undo, #fpd-redo').click(function(evt) {

		evt.preventDefault();

		if($(this).attr('id') == 'fpd-undo') {
			fancyProductDesigner.currentViewInstance.undo();
		}
		else {
			fancyProductDesigner.currentViewInstance.redo();
		}

	});

	//center fabric element
	$('#fpd-center-horizontal, #fpd-center-vertical').click(function(evt) {

		evt.preventDefault();

		var currentElement = stage.getActiveObject();
		if(currentElement) {

			if($(this).attr('id') == 'fpd-center-horizontal') {
				fancyProductDesigner.currentViewInstance.centerElement(true);
			}
			else {
				fancyProductDesigner.currentViewInstance.centerElement(false, true);
			}

			currentElement.setCoords();
			_setFormFields(currentElement);
		}

	});

	$('#fpd-ruler').click(function(evt) {

		evt.preventDefault();

		fpdActions.doAction($(this));

	});


	//submit form
	$('#fpd-save-layers').click(function(evt) {

		evt.preventDefault();

		if(productCreated) {
			$('[name="save_elements"]').click();
		}

	});

	//let the page know that elements are now saved
	$('input[name="save_elements"]').click(function() {

		fancyProductDesigner.deselectElement();
		changesAreSaved = true;

	});

	$('#fpd-preview-buttons span').click(function() {

		var $this = $(this);

		$this.toggleClass('fpd-active').siblings().removeClass('fpd-active');

		$fpd.removeClass(function (index, className) {
		    return (className.match (/(^|\s)fpd-responsive-\S+/g) || []).join(' '); //remove class that starts with fpd-responsive-
		}).removeClass('fpd-responsive');

		if($this.hasClass('fpd-active')) {

			$fpd.addClass('fpd-responsive fpd-responsive-'+$this.data('value'));
			viewInstance.options.responsive = true;
			viewInstance.resetCanvasSize();

		}
		else {
			viewInstance.options.responsive = false;
			viewInstance.resetCanvasSize();
		}

	});


	//init fancy product designer
	$fpd.on('ready', function() {

		fancyProductDesigner = $(this).data('instance');

		fpdActions = new FPDActions(fancyProductDesigner, false);

		$.ajax({
			url: fpd_admin_opts.adminAjaxUrl,
			data: {
				action: 'fpd_loadview',
				_ajax_nonce: fpd_admin_opts.ajaxNonce,
				view_id: $fpd.data('viewid')
			},
			type: 'post',
			dataType: 'json',
			success: function(data) {

				initialProduct = [{
					title: 'preview',
					thumbnail: '',
					elements: [],
					options: {}
				}];

				if(data && data.elements) {

					//V2 - views are serialized - string is newer
					var elements = typeof data.elements === 'string' ? JSON.parse(data.elements) : data.elements;

					//check that all number stings are parsed as number, <V3.0
					for(var i=0; i < elements.length; ++i) {

						var elementParams = elements[i].parameters;

						$.each(elementParams, function(key, value) {
							elementParams[key] = isNaN(value) ? value : Number(value);
						});

						var pbOpts = FPDUtil.rekeyDeprecatedKeys($.extend({}, elementParams));

						elements[i].parameters.pbOptions = pbOpts;

					}

					initialProduct[0].elements = elements;
				}

				fancyProductDesigner.toggleSpinner(true, fpd_product_builder_opts.loading);
				fancyProductDesigner.loadProduct(initialProduct);

			}
		});

	})
	.on('elementAdd', function(evt, element) {

		if(element.adminLocked) {
			element.set('evented', false);
		}

		var type = FPDUtil.getType(element.type),
			imageHTML = type === 'image' ? "<img src='"+element.source+"' />" : "",
			imageToolHTML = type === 'image' ? "<a href='#' class='fpd-change-image'><i class='fpd-admin-icon-repeat'></i></a>" : "",
			lockedIcon = element.adminLocked ? "fpd-admin-icon-lock" : "fpd-admin-icon-lock-open";

			//new element added (pbOptions = product builder options)
			if(element.pbOptions === undefined) {

				element.center();
				element.setCoords();
				stage.renderAll();

				//set default props
				element.removable = element.draggable = element.rotatable = element.resizable = element.zChangeable = false;
				element.pbOptions = {left: Math.round(element.left), top: Math.round(element.top)};

			}

		$elementLists.append("<div id='"+element.id+"' class='fpd-layer-item fpd-layer-item--"+type+"'><input type='text' name='element_titles[]' value='"+element.title+"' />"+imageHTML+"<textarea name='element_sources[]'>"+element.source+"</textarea><div class='fpd-layer-item-tools'>"+imageToolHTML+"<a href='#' class='fpd-lock-element'><i class='"+lockedIcon+"'></i></a><a href='#' class='fpd-trash-element'><i class='fpd-admin-icon-bin'></i></a></div><input type='hidden' name='element_types[]' value='"+type+"'/><input type='hidden' name='element_parameters[]' value='"+JSON.stringify(element.pbOptions)+"'/></div>");

		if(productCreated) {
			stage.setActiveObject(element);
			_setFormFields(element);
		}

		if(printingBoxRect) {
			printingBoxRect.bringToFront();
		}

	})
	.on('productCreate', function() {

		viewInstance = fancyProductDesigner.currentViewInstance;
		stage = viewInstance.stage;

		$parametersPanel.on('click', '.radykal-tabs-nav > a', function() {
			stage.calcOffset().renderAll();
		});

		//create a bounding box rectangle
		boundingBoxRect = new fabric.Rect({
			stroke: 'blue',
			strokeWidth: 1,
			strokeDashArray: [5, 5],
			fill: false,
			selectable: false,
			visible: false,
			evented: false,
			selectable: false,
			transparentCorners: false,
			cornerSize: 20,
			originX: 'left',
			originY: 'top'
		});
		stage.add(boundingBoxRect);

		if(fpdGlobalProductBuilderOpts.printingBox) {

			var printingBox = fpdGlobalProductBuilderOpts.printingBox;
			_updatePrintingBox(printingBox.width, printingBox.height, printingBox.left, printingBox.top);

		}

		var _elementSelected = function(opts) {

			if(opts.target.name == 'printing-box') {
				return;
			}

			if($currentListItem && opts.target.id !== $currentListItem.attr('id')) {
				$currentListItem.find('textarea, input').blur();
			}

			_updateFormState();
			_setFormFields();
			_setBoundingBox();

			setTimeout(function(){
				opts.target.setCoords();
				stage.calcOffset().renderAll();
			}, 50);

			opts.target.drawBorders(stage.contextContainer);
			opts.target.drawControls(stage.contextContainer);

		};

		stage.on({
			'mouse:down': function(opts) {
				if(opts.target === undefined) {
					_updateFormState();
				}
			},
			'selection:updated': _elementSelected, //Fabric V2.1
			'object:selected': _elementSelected,
			'object:moving': function(opts) {

				if(opts.target.name === 'printing-box' && printingBoxRect) {

					$printingBoxToolbar.find('[name="left"]').val(parseInt(printingBoxRect.left));
					$printingBoxToolbar.find('[name="top"]').val(parseInt(printingBoxRect.top));

				}

			},
			'object:scaling': function(opts) {

				if(opts.target.name === 'printing-box' && printingBoxRect) {
					$printingBoxToolbar.find('[name="scale"]').val(printingBoxRect.scaleX);
					$printingBoxToolbar.find('#fpd-printing-box-pixels').html(parseInt(printingBoxRect.width)+ ' x ' + parseInt(printingBoxRect.height) +' pixels');
				}

			}
		});

		if($fpd.data('viewmask') && typeof $fpd.data('viewmask') === 'object') {
			viewInstance.setMask($fpd.data('viewmask'));
		}

		$('#fpd-manage-elements').children('.fpd-ui-blocker').remove();
		productCreated = true;

	})
	.on('elementRemove', function(evt, element) {

		$elementLists.children('#'+element.id).remove();
		_updateFormState();
		changesAreSaved = false;

	})
	.on('elementModify', function(evt, element, parameters) {

		if(productCreated) {

			if(parameters.text) {
				$elementLists.children('.fpd-layer-item#'+element.id+'')
				.find('[name="element_titles[]"], [name="element_sources[]"]')
				.val(parameters.text);
			}
			//only numeric values
			else if(parameters.hasOwnProperty('left') ||
					parameters.hasOwnProperty('angle') ||
					parameters.hasOwnProperty('scaleX') ||
					parameters.hasOwnProperty('fontSize')
				) {
				_setFormFields(element);
			}

		}

	})
	.on('undoRedoSet', function(evt, undos, redos) {

		_toggleUndoRedoBtn(undos, redos);

	});

	//initi FPD
	new FancyProductDesigner($fpd, pluginsOptions);

	//add new element buttons handler
	$('#fpd-add-image-element, #fpd-add-text-element, #fpd-add-curved-text-element, #fpd-add-upload-zone, #fpd-add-text-box-element').click(function(evt) {
		evt.preventDefault();

		fancyProductDesigner.deselectElement();
		$currentListItem = null;

		var $this = $(this);

		radykalPrompt({placeholder: fpd_product_builder_opts.enterTitlePrompt}, function(title) {

			if(title === false) {
				fpdMessage(fpd_admin_opts.enterTitlePrompt, 'error');
			}
			else if(title !== null) {

				var defaultProps = {
					removable: false,
					draggable: false,
					rotatable: false,
					resizable: false,
					zChangeable: false,
				};

				//add image or upload zone
				if($this.attr('id') == 'fpd-add-image-element' || $this.attr('id') == 'fpd-add-upload-zone') {

					var addUploadZone = $this.attr('id') == 'fpd-add-upload-zone';

			        mediaUploader = wp.media({
			            title: fpd_product_builder_opts.chooseElementImageTitle,
			            button: {
			                text: fpd_product_builder_opts.set
			            },
			            multiple: false
			        });

					mediaUploader.elementTitle = title;
					mediaUploader.on('select', function() {

						if(addUploadZone) {
							defaultProps.uploadZone = 1;
							defaultProps.adds_uploads = defaultProps.adds_texts = defaultProps.adds_designs = 1;
						}

						fancyProductDesigner.addElement(
							'image',
							mediaUploader.state().get('selection').toJSON()[0].url,
							mediaUploader.elementTitle,
							defaultProps
						);

						mediaUploader = null;
			        });

			        mediaUploader.open();

				}
				//add text
				else {

					if($this.attr('id') == 'fpd-add-curved-text-element') {
						defaultProps.curved = 1;
						defaultProps.textAlign = 'center';
					}
					else if($this.attr('id') == 'fpd-add-text-box-element') {
						defaultProps.textBox = true;
						defaultProps.width = 200;
						defaultProps.height = 100;
					}

					fancyProductDesigner.addElement(
						'text',
						title,
						title,
						defaultProps
					);

				}

			}

		});

    });


	//form change handler
	$parametersForm.on('change keyup', 'input:not([name="hidden-colors"]), select', function(evt) {

		var $option = $(this),
			type = $option.attr('type');

		if($option.hasClass('fpd-svg-options') && $option.attr('name') == 'colors') {

			_toggleFormFields('.fpd-color-options:first input', !$option.is(':checked'));

		}

		if(viewInstance && viewInstance.currentElement && updatingFormFields === false) {

			var params = {},
				key = $option.attr('name'),
				value = $option.val();

			if(type == 'checkbox') {

				params[key] = $option.is(':checked') ? 1 : 0;
				if($option.hasClass('fpd-toggle-button')) {
					params[key] = $option.is(':checked') ?  value : $option.data('unchecked');
				}

				if($option.data('checkedsel')) {
					$($option.data('checkedsel')).toggle($option.is(':checked'));
				}

				if($option.data('uncheckedsel')) {
					$($option.data('uncheckedsel')).toggle(!$option.is(':checked'));
				}

			}
			else if(key == 'fill' && !FPDUtil.isHex(value)) {
				params.fill = false;
			}
			else {

				if(key == 'scaleX' && $('#fpd-scale-locker').hasClass('fpd-admin-icon-lock')) {
					params.scaleY = Number(value);
				}

				params[key] = isNaN(value) || value === '' ? value : Number(value);
			}

			_optionHandling($option);

			viewInstance.setElementParameters(params);

			_setParameters();
			_setBoundingBox();

		}

	})
	.on('keypress', function(evt) {

		if (evt.keyCode == 13) {
			$(evt.target).change();
			return false;
		}

	});

	var $maskToolbar = $('#fpd-mask-toolbar'),
		maskOptionsLoaded = false;

	$('#fpd-edit-mask').click(function() {

		if($maskToolbar.is(':hidden')) {

			toggleModalLoader($maskToolbar, true);
			$.ajax({
				url: fpd_admin_opts.adminAjaxUrl,
				data: {
					action: 'fpd_loadviewoptions',
					_ajax_nonce: fpd_admin_opts.ajaxNonce,
					view_id: $fpd.data('viewid')
				},
				type: 'post',
				dataType: 'json',
				success: function(data) {

					if(data && data.options) {

						currentViewOptions = data.options;
						if(!_.isObject(currentViewOptions)) {
							currentViewOptions = JSON.parse(currentViewOptions);
						}

						if(currentViewOptions.mask) {

							fpdFillFormWithObject(currentViewOptions.mask, $maskToolbar.find('table'));

							if(currentViewOptions.mask.url) {
								$maskToolbar.find('.fpd-single-image-upload img').remove();
								$maskToolbar.find('.fpd-single-image-upload').append('<img src="'+currentViewOptions.mask.url+'" />');
							}
						}

					}

					toggleModalLoader($maskToolbar);

				}
			});

			$maskToolbar.addClass('fpd-show');
		}
		else {
			$maskToolbar.removeClass('fpd-show');
		}

	});

	$maskToolbar.find('.fpd-single-image-upload').click(function(evt) {

		evt.preventDefault();

		mediaUploader = wp.media({
            title: fpd_product_builder_opts.chooseElementImageTitle,
            button: {
                text: fpd_product_builder_opts.set
            },
            multiple: false
        });

		mediaUploader.on('select', function() {

			var imageURL = mediaUploader.state().get('selection').toJSON()[0].url;

			if($.inArray('svg', imageURL.split('.')) != -1) {
				$maskToolbar.find('.fpd-single-image-upload img').remove();
				$maskToolbar.find('.fpd-single-image-upload').append('<img src="'+imageURL+'" />');
				$maskToolbar.find('.fpd-single-image-upload input').val(imageURL);

				fancyProductDesigner.currentViewInstance.setMask({
					url: imageURL
				}, function(maskObject) {
					$maskToolbar.find('input[type="number"]').val('');
				});
			}
			else {
				fpdMessage(fpd_product_builder_opts.mask_svg_alert, 'error');
			}

			mediaUploader = null;
        });

        mediaUploader.open();

	});

	//remove view image
	$maskToolbar.find('.fpd-single-image-upload > .fpd-remove').click(function(evt) {

		evt.preventDefault();
		evt.stopPropagation();

		$maskToolbar.find('.fpd-single-image-upload img').remove();
		$maskToolbar.find('.fpd-single-image-upload input').val('');
		fancyProductDesigner.currentViewInstance.setMask(null);

	});

	$maskToolbar.find('input[type="number"]').change(function(evt) {

		if(viewInstance && viewInstance.maskObject) {
			viewInstance.maskObject[this.name] = Number(this.value);
			viewInstance.stage.renderAll();
		}

	});

	$('#fpd-save-mask-options').click(function() {

		var $formFields = $maskToolbar.find('input'),
			maskValues = fpdSerializeObject($formFields);

		currentViewOptions = currentViewOptions === null || currentViewOptions.length === 0 ? {} : currentViewOptions;

		var optionsData = $.extend({}, currentViewOptions, {mask: maskValues});
		optionsData = fpdIsEmptyObject(optionsData) ? '' : optionsData;

		toggleModalLoader($maskToolbar, true);
		$.ajax({
			url: fpd_admin_opts.adminAjaxUrl,
			data: {
				action: 'fpd_editview',
				_ajax_nonce: fpd_admin_opts.ajaxNonce,
				id: $fpd.data('viewid'),
				options: JSON.stringify(optionsData)
			},
			type: 'post',
			dataType: 'json',
			success: function(data) {

				toggleModalLoader($maskToolbar);

				if(!_.isUndefined(data) && !_.isUndefined(data.columns)) {

					if(!_.isUndefined(data.columns.options)) {
						$maskToolbar.removeClass('fpd-show');
						fpdMessage(data.message, 'success');
					}
					else {
						fpdMessage(fpd_admin_opts.tryAgain, 'error');
					}

				}

			}
		});

	});

	var $printingBoxToolbar = $('#fpd-printing-box-toolbar'),
		printingBoxOptionsLoaded = false;

	$('#fpd-edit-printing-box').click(function() {

		if($printingBoxToolbar.is(':hidden')) {

			toggleModalLoader($printingBoxToolbar, true);
			$.ajax({
				url: fpd_admin_opts.adminAjaxUrl,
				data: {
					action: 'fpd_loadviewoptions',
					_ajax_nonce: fpd_admin_opts.ajaxNonce,
					view_id: $fpd.data('viewid')
				},
				type: 'post',
				dataType: 'json',
				success: function(data) {

					if(data && data.options) {

						currentViewOptions = data.options;
						if(!_.isObject(currentViewOptions)) {
							currentViewOptions = JSON.parse(currentViewOptions);
						}

						$printingBoxToolbar.find('[name="left"]').val(parseInt(printingBoxRect.left));
						$printingBoxToolbar.find('[name="top"]').val(parseInt(printingBoxRect.top));
						$printingBoxToolbar.find('[name="scale"]').val(printingBoxRect.scaleX);
						$printingBoxToolbar.find('#fpd-printing-box-pixels').html(parseInt(printingBoxRect.width)+ ' x ' + parseInt(printingBoxRect.height) +' pixels');

						printingBoxRect.evented = true;
						stage.setActiveObject(printingBoxRect);

					}

					toggleModalLoader($printingBoxToolbar);

				}
			});

			$printingBoxToolbar.addClass('fpd-show');
		}
		else {

			printingBoxRect.evented = false;
			stage.discardActiveObject().renderAll();
			$printingBoxToolbar.removeClass('fpd-show');

		}

	});

	$printingBoxToolbar.find('input').change(function() {

		if(printingBoxRect) {

			if(this.name === 'scale') {
				printingBoxRect.scaleX = parseFloat(this.value);
				printingBoxRect.scaleY = parseFloat(this.value);
				$printingBoxToolbar.find('#fpd-printing-box-pixels').html(parseInt(printingBoxRect.width)+ ' x ' + parseInt(printingBoxRect.height) +' pixels');
			}
			else {
				printingBoxRect[this.name] = parseInt(this.value);
			}

			printingBoxRect.setCoords();
			stage.renderAll();

		}

	});

	$('#fpd-save-printing-box-options').click(function() {

		var bbBoundingRect = printingBoxRect.getBoundingRect();
		Object.keys(bbBoundingRect).forEach(function(key) {
			bbBoundingRect[key] = parseInt(bbBoundingRect[key]);
		});

		currentViewOptions = currentViewOptions === null || currentViewOptions.length === 0 ? {} : currentViewOptions;

		var optionsData = $.extend({}, currentViewOptions, {printing_box: bbBoundingRect});
		optionsData = fpdIsEmptyObject(optionsData) ? '' : optionsData;

		toggleModalLoader($printingBoxToolbar, true);

		$.ajax({
			url: fpd_admin_opts.adminAjaxUrl,
			data: {
				action: 'fpd_editview',
				_ajax_nonce: fpd_admin_opts.ajaxNonce,
				id: $fpd.data('viewid'),
				options: JSON.stringify(optionsData)
			},
			type: 'post',
			dataType: 'json',
			success: function(data) {

				toggleModalLoader($printingBoxToolbar);

				if(!_.isUndefined(data) && !_.isUndefined(data.columns)) {

					if(!_.isUndefined(data.columns.options)) {
						$maskToolbar.removeClass('fpd-show');
						fpdMessage(data.message, 'success');
					}
					else {
						fpdMessage(fpd_admin_opts.tryAgain, 'error');
					}

				}

			}
		});

	});

	var _updatePrintingBox = function(width, height, left, top) {

		if(width && height) {

			var bbWidth = parseInt(width),
				bbHeight = parseInt(height);

			if(printingBoxRect) {
				stage.remove(printingBoxRect);
			}

			printingBoxRect = new fabric.Rect({
				stroke: fpd_product_builder_opts.bounding_box_color,
				strokeWidth: 1.5,
				strokeLineCap: 'square',
				strokeDashArray: [10, 10],
				fill: false,
				borderColor: 'transparent',
				transparentCorners: true,
				cornerColor: '#fff',
				cornerIconColor: '#000',
				lockUniScaling: true,
				lockRotation: true,
				resizable: true,
				evented : false,
				centeredScaling: true,
				cornerSize: 20,
				originX: 'left',
				originY: 'top',
				name: 'printing-box',
				width: bbWidth,
				height: bbHeight
			});

			stage.add(printingBoxRect)

			if(_.isNumber(top) && _.isNumber(left)) { // set existing printing box

				printingBoxRect.set('left', left);
				printingBoxRect.set('top', top);

			}
			else { //set new printing box and scale to fit in canvas and center

				if(bbWidth > bbHeight) {
					if(bbWidth > viewInstance.options.stageWidth) {

						var scale = (viewInstance.options.stageWidth * 0.8) / bbWidth;
						printingBoxRect.set('width', viewInstance.options.stageWidth * 0.8);
						printingBoxRect.set('height', scale * bbHeight);

					}
				}
				else {
					if(bbHeight > viewInstance.options.stageHeight) {

						var scale = (viewInstance.options.stageHeight * 0.8) / bbHeight;
						printingBoxRect.set('height', viewInstance.options.stageHeight * 0.8);
						printingBoxRect.set('width', scale * bbWidth);

					}
				}

				printingBoxRect.center();
			}

			printingBoxRect.setCoords();
			stage.renderAll();

			$('#fpd-edit-printing-box').removeClass('radykal-disabled');

		}
		else {

			if(printingBoxRect) {
				stage.remove(printingBoxRect);
				printingBoxRect = null;
			}

			$('#fpd-edit-printing-box').addClass('radykal-disabled');

		}

	};

	//enable editing of the form when an element is selected in stage
	function _updateFormState() {

		updatingFormFields = true;

		var currentElement = stage.getActiveObject();

		$('.tm-input').tagsManager('empty');

		//object is selected
		if(currentElement && currentElement.selectable) {

			$parametersForm.find('input, select').prop('disabled', false);
			$elementLists.children('.fpd-layer-item').removeClass('fpd-active-item');
			$currentListItem = $elementLists.children('#'+currentElement.id).addClass('fpd-active-item');

			_hideTab('color-options', FPDUtil.elementIsColorizable(currentElement) === false);
			_hideTab('text-options', FPDUtil.getType(currentElement.type) !== 'text');
			_hideTab('upload-zone-options', !currentElement.uploadZone);
			_hideTab('bb-options', Boolean(currentElement.uploadZone));

			if(FPDUtil.elementIsColorizable(currentElement) === 'svg') {
				//if every path color is false or colors are set
				_toggleFormFields('.fpd-color-options:first input', Number(currentElement.colors) === 0 || $.isArray(currentElement.colors));
			}
			else {
				_toggleFormFields('.fpd-color-options input', FPDUtil.elementIsColorizable(currentElement));
			}

			$('.fpd-color-options').not(':first').toggleClass('radykal-hidden', FPDUtil.elementIsColorizable(currentElement) === 'svg');
			_toggleFormFields('.fpd-svg-options', FPDUtil.elementIsColorizable(currentElement) === 'svg');
			_toggleFormFields('.fpd-upload-zone-hidden', !currentElement.uploadZone);
			_toggleFormFields('[name="curvable"], [name="resizable"]', currentElement.type !== 'textbox');
			_toggleFormFields('[name="minFontSize"], [name="maxFontSize"], [name="widthFontSize"]', currentElement.type !== 'textbox');
			_toggleFormFields('.fpd-text-hidden', FPDUtil.getType(currentElement.type) !== 'text');
			_toggleFormFields('[name="advancedEditing"]', FPDUtil.getType(currentElement.type) === 'image');


			$('.fpd-curved-text-opts').toggleClass('radykal-hidden', !currentElement.curved);
			if(currentElement.type === 'curvedText') {
				_toggleFormFields('[name="scaleX"], [name="scaleY"]', true);
			}

			$('.fpd-text-box-opts').toggleClass('radykal-hidden', !currentElement.textBox);
			if(currentElement.type === 'textbox') {
				_toggleFormFields('[name="scaleX"], [name="scaleY"]', false);
			}

			$('#fpd-element-toolbar .fpd-element-toggle')
			.add($parametersPanel)
			.removeClass('radykal-disabled');

		}
		//no selected objecct
		else {

			$parametersPanel.addClass('radykal-disabled');
			$('#fpd-element-toolbar .fpd-element-toggle').addClass('radykal-disabled');
			$elementLists.children('.fpd-layer-item').removeClass('fpd-active-item');
			boundingBoxRect.visible = false;
			$currentListItem = null;

		}

		$parametersPanel.find('.radykal-tabs-nav > a:first').click();

	};

	//update form fields when element is changed via product stage
	function _setFormFields(element) {

		var formElementOpts = {},
			productBuilderOpts;

		if(element === undefined) { //get element properties from hidden input
			var elemParams = $currentListItem.children('[name="element_parameters[]"]').val();
			formElementOpts = JSON.parse(elemParams) || {},
			productBuilderOpts = formElementOpts;
		}
		else { //get properties from element
			productBuilderOpts = element;
		}

		$parametersForm.find('input, select').each(function(i, option) {

			var $option = $(option),
				type = $option.attr('type'),
				prop = $option.attr('name');

			if(type == 'text' || type == 'number') {

				if(productBuilderOpts.hasOwnProperty(prop) && productBuilderOpts[prop] !== false) {
					var value = productBuilderOpts[prop];

					if(type == 'number') {
						value = $option.hasClass('fpd-allow-dots') ? parseFloat(value).toFixed(2) : parseInt(value);
					}
					else {
						value = isNaN(value) ? value : '';
					}

					$option.val(value);

					if($option.prev('.ui-slider').length > 0) {
						$option.prev('.ui-slider').slider('value', Number(value));
					}

					if($option.hasClass('wp-color-picker')) {
						$option.wpColorPicker('color', value);
					}


				}
				else {
					$option.val('');
				}

			}
			else if(type == 'checkbox') {

				if($option.hasClass('fpd-toggle-button')) {
					$option.prop('checked', productBuilderOpts[prop] == $option.val());
					$option.prevAll('.fpd-'+$option.val()+'.button').toggleClass('active', $option.is(':checked'));
				}
				else {

					if(prop === 'colors') {
						$option.prop('checked', productBuilderOpts[prop] == 1);
					}
					else {
						$option.prop('checked', Boolean(productBuilderOpts[prop]) || false);
					}

					if($option.data('checkedsel')) {
						$($option.data('checkedsel')).toggle($option.is(':checked'));

					}

					if($option.data('uncheckedsel')) {
						$($option.data('uncheckedsel')).toggle(!$option.is(':checked'));

					}

				}

			}
			else if(type == 'radio') {

				if(formElementOpts[prop] !== undefined) {
					$option.prop('checked', formElementOpts[prop] == $option.val());
				}

				if(prop == 'colors' && isNaN(formElementOpts[prop])) {
					$option.prop('checked', Number($option.val()) === 0);
				}

			}
			else if($option.is('select')) {

				if(prop == 'fontFamily') {
					$option.val(productBuilderOpts.hasOwnProperty(prop) ? productBuilderOpts[prop] : fpd_product_builder_opts.defaultFont);
				}
				else if(prop == 'designCategories[]') {
					$option.val(productBuilderOpts.hasOwnProperty(prop) ? productBuilderOpts[prop] : '');
				}
				else {
					$option.val(productBuilderOpts.hasOwnProperty(prop) ? productBuilderOpts[prop] : $option.val());
				}

				//
				if($option.attr('multiple')) {
					if($.isArray(productBuilderOpts[prop])) {

						productBuilderOpts[prop].forEach(function(val) {
							$option.children('option[value="'+val+'"]').prop('selected', true);
						});
						$option.trigger('change');

					}

				}
				else {
					$option.children('option[value="'+productBuilderOpts[prop]+'"]').prop('selected', true);
				}

				if(element === undefined) {
					$option.change();
				}

			}
			else if($option.hasClass('fpd-radio-buttons')) {

				$option.val((productBuilderOpts[prop] || $option.val()))
				.prevAll('.button').removeClass('active')
				.filter('[data-value="'+(productBuilderOpts[prop] || $option.val())+'"]').addClass('active');

			}

			_optionHandling($option);


		});

		//set color tags
		if(productBuilderOpts.colors && productBuilderOpts.colors.length > 0 && unescape(productBuilderOpts.colors).charAt(0) == '#') {

			var colorArray = unescape(productBuilderOpts.colors).split(',');
			for(var i=0; i < colorArray.length; ++i) {
				$('.tm-input').tagsManager('pushTag', colorArray[i]);
			}

		}

		stage.calcOffset().renderAll();
		updatingFormFields = false;
		_setParameters();

	};

	function _hideTab(id, hide) {

		hide = hide === undefined ? true : hide;
		hide = Boolean(hide);

		$parametersPanel.find('.radykal-tabs-nav > [href="'+id+'"]').toggleClass('radykal-hidden', hide);
		$parametersPanel.find('.radykal-tabs-content > [data-id="'+id+'"]').find('input, select').prop('disabled', hide);

	};

	function _toggleFormFields(selector, toggle) {

		toggle = toggle === undefined ? false : toggle;
		toggle = Boolean(toggle);

		$(selector).each(function(i, field) {

			var $field = $(field);

			//if input:text or number, reset input
			if(!toggle && ($field.is('input:text') || $field.attr('type') == 'number')) {
				$field.val('');
			}

			$field.prop('disabled', !toggle);

			if($field.parent().hasClass('fpd-group-field')) {
				$field.parent().toggle(toggle);
			}
			else {
				$field.parents('tr:first').toggle(toggle);
			}


		});

	};

	function _setBoundingBox() {

		if(!$currentListItem) {
			return false;
		}

		var target = JSON.parse($currentListItem.children('input[name="element_parameters[]"]').val());

		boundingBoxRect.visible = false;

		if(target.bounding_box_control && target.bounding_box_by_other && target.bounding_box_by_other.length > 0) {

			var targetElement = viewInstance.getElementByTitle(target.bounding_box_by_other);

			if(targetElement) {
				var boundingRect = targetElement.getBoundingRect();
				boundingBoxRect.left = boundingRect.left;
				boundingBoxRect.top = boundingRect.top;
				boundingBoxRect.width = boundingRect.width;
				boundingBoxRect.height = boundingRect.height;
				boundingBoxRect.visible = true;
			}

		}
		else if(target.bounding_box_x !== undefined &&
				target.bounding_box_y !== undefined &&
				target.bounding_box_width !== undefined &&
				target.bounding_box_height !== undefined) {

			boundingBoxRect.left = parseInt(target.bounding_box_x);
			boundingBoxRect.top = parseInt(target.bounding_box_y);
			boundingBoxRect.width = parseInt(target.bounding_box_width);
			boundingBoxRect.height = parseInt(target.bounding_box_height);
			boundingBoxRect.visible = true;

		}

		boundingBoxRect.setCoords();
		stage.renderAll();

	};

	function _setParameters() {

		if(!$currentListItem) {
			return;
		}

		var parameters = {};
		$parametersForm.find('input, select').each(function(i, option) {

			var $option = $(option);

			if(!$option.is(':disabled') && $option.val() !== '' && $option.attr('name') !== undefined) {

				var	key = $option.attr('name'),
					value = $option.val(),
					type = $option.attr('type');

				if(key === 'hidden-colors') {
					key = 'colors';
				}

				if(type == 'number' || type == 'text') {
					value = isNaN(value) ? value : Number(value);
				}
				else if(type == 'checkbox') {

					value = $option.is(':checked') ? 1 : 0;
					if($option.hasClass('fpd-toggle-button')) {
						value = $option.is(':checked') ? $option.val() : $option.data('unchecked');
					}
				}
				else if(type == 'radio') {
					value = $option.is(':checked') ? (isNaN($option.val()) ? $option.val() : Number($option.val())) : null;
				}

				if(value !== null) {
					parameters[key] = value;
				}

			}

		});

		$currentListItem.children('input[name="element_parameters[]"]').val(JSON.stringify(parameters));
		//console.log(parameters);

		changesAreSaved = false;

	};

	var _toggleUndoRedoBtn = function(undos, redos) {

		if(undos.length === 0) {
		  	$('#fpd-undo').addClass('radykal-disabled');
  		}
  		else {
	  		$('#fpd-undo').removeClass('radykal-disabled');
  		}

  		if(redos.length === 0) {
	  		$('#fpd-redo').addClass('radykal-disabled');
  		}
  		else {
	  		$('#fpd-redo').removeClass('radykal-disabled');
  		}

	};

	var _optionHandling = function($option) {

		if($option.is('select')) {

			if($option.data('toggle')) {

				var toggles = $option.data('toggle').split(',');
				for(var i=0; i<toggles.length; ++i) {

					var toggleEntry = toggles[i].split('=');
					_toggleFormFields(toggleEntry[0], $option.val() == toggleEntry[1]);

				}

			}

		}

	};

	//check if changes are saved before page unload
	/*$(window).on('beforeunload', function () {
		if(!changesAreSaved) {
			return fpd_product_builder_opts.notChanged;
		}
	});*/

});