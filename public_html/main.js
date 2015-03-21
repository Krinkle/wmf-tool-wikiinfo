(function (K, $) {
	var $input = $('#ot-form-wikiids'),
		$submit = $('#ot-form-submit'),
		$form = $('#ot-form'),
		resultNode = $('#ot-result')[0],
		lastVal,
		jqXhr;

	function handleInput(val) {
		if (jqXhr) {
			return;
		}

		val = $.trim(val);
		if (!val) {
			return;
		}

		// Ensure de-duplication between submit-click and
		// input-blur, and input-change and input-blur.
		if (val === lastVal) {
			return;
		}
		lastVal = val;

		$submit.prop('disabled', true).addClass('ot-is-active');
		$form.addClass('ot-is-active');
		jqXhr = $.ajax({
			url: K.baseTool.basePath,
			type: 'POST',
			data: {
				format: '_tool',
				_tool: 'ajax',
				wikiids: val
			},
			dataType: 'html'
		}).done(function (html) {
			resultNode.innerHTML = html;
			if (history.replaceState) {
				history.replaceState(null, null, './?wikiids=' + val);
			}
		}).always(function () {
			jqXhr = null;
			$submit.prop('disabled', false).removeClass('ot-is-active');
			$form.removeClass('ot-is-active');
		}).fail(function () {
			$form.submit();
		});
	}

	$input.on('change blur', function () {
		handleInput(this.value);
	});

	$submit.on('click', function (e) {
		handleInput($input.val());
		e.preventDefault();
	})
	.addClass('ot-has-spinner')
	.append('<span class="ot-spinner"> <i class="glyphicon-spin glyphicon glyphicon-refresh"></i></span>');

}(KRINKLE, jQuery));
