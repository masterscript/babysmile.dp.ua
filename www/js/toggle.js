        $(document).ready(function() {
   $('#effect').hide();
   $('#effect').children().hide;
});
	$(function() {
		// run the currently selected effect
		function runEffect() {
			// get effect type from
			var selectedEffect = $( "#effectTypes" ).val();

			// most effect types need no options passed by default
			var options = {};
			// some effects have required parameters
			if ( selectedEffect === "scale" ) {
				options = { percent: 0 };
			} else if ( selectedEffect === "size" ) {
				options = { to: { width: 200, height: 60 } };
			}

			// run the effect
			$( "#effect" ).toggle( selectedEffect, options, 500 );
		};

		// set effect from select menu value
		$( "#desctoggle" ).click(function() {
			runEffect();
			return false;
		});
	});
