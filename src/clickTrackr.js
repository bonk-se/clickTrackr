/**
 * @preserve clickTrackr - http://bonk.se/clickTrackr/
 * @version 0.6.1
 * @author miken@bonk.se
 */
(function() {
	/**
	 * Clicked position stored as a string (JSON) with x,y-values
	 * @var {String}
	 */
	var pos = '';
	/**
	 * Number of stored positions
	 * @var {int}
	 */
	var posCount = 0;
	/**
	 * Reference element (jQuery object)
	 * @var {Object}
	 */
	var refObj;

	/**
	 * Config for clickTrackr.
	 * Modify these parameters to fit your default setup, so you
	 * only have run call bCT.init on special occations.
	 * @var {Object}
	 */
	var conf = {
		/**
		 * Track all clicks on the page or just the one that the user clicks to unload it.
		 * @var {bool}
		 */
		all: false,
		/**
		 * Frequens - Only initiate the tracking for every N'th time.
		 * Around 2-3.000 clicks are ideal for generating a heatmap for a page,
		 * so try to set your frequency accordingly.
		 * 1 = initiated every time, 30 = every 30th page view
		 * @var {int}
		 */
		freq: 1,
		/**
		 * Element id where reference point (0, 0) should be.
		 * If no refId is set, the browser windows own coordinates will be used.
		 * @var {String}
		 */
		refId: null,
		/**
		 * Log-URL where the data is stored. Has to output some image data, like an empty 1x1-gif.
		 * If you want extra parameters in the query-string, add them here.
		 * @var {String} 
		 */
		log: '/clickTrackr/log.php?',
		/**
		 * Current URL where the clickTrackr is running.
		 * It's recommended to exclude the query-string from the URL when storing the data.
		 * clickTrackr Logger does this by default.
		 * @var {String}
		 */
		url: document.location.href,
		/**
		 * Max value of X. Positions over the value will not be tracked.
		 * Negative number means no limit
		 * @var {int}
		 */
		maxX: -1,
		/**
		 * Max value of Y. Positions over the value will not be tracked.
		 * Negative number means no limit
		 * @var {int}
		 */
		maxY: -1
	};

	/**
	 * Initiate clickTracker.
	 * Can be called manually to overload default settings.
	 * @example
	 * bCT.init({freq:2, maxX:600});
	 *
	 * @param {Object} conf   Overload default config for clickTrackr (all optional)
	 */
	var init = function(custom) {
		$.extend(conf, custom);
		/* Check frequence */
		if (1 == conf.freq || 1 == Math.floor(Math.random() * conf.freq + 1)) {
			/* Bind events required for clickTrackr, when document is ready */
			$(function(){
				$('body').click(capturePosition);
				window.onbeforeunload = sendData;
			});
		}
	};

	/**
	 * Store the current pointer-position, if the positions are correct.
	 * @param {Object} event   The jQuery-event that triggered this call
	 * @return {bool}   Always returns true
	 */
	var capturePosition = function(event) {
		try {
			var x = event.pageX;
			var y = event.pageY;
			if (conf.refId) {
				refObj = refObj || $('#'+conf.refId);
				var offset = refObj.offset();
				x = x - offset.left;
				y = y - offset.top;
			}
			if (0 <= x && (0 < conf.maxX && x <= conf.maxX || 0 > conf.maxX)
				&& 0 <= y && (0 < conf.maxY && y <= conf.maxY || 0 > conf.maxY))
			{
				if (conf.all) {
					pos += '['+x+','+y+'],';
					posCount++;
					if (10 <= posCount) {
						sendData();
					}
				} else {
					pos = '['+x+','+y+'],';
				}
			}
		} catch (oops) {};
		return true;
	};

	/**
	 * Send the position(s) to the measureUrl.
	 * The parameters u, p and t are added to the log-URL. 
	 */
	var sendData = function() {
		try {
			if (5 <= pos.length) {
				var d = new Date;
				var im = new Image(1,1);
				im.onload = function(){};
				im.src = conf.log+'&u='+escape(conf.url)+'&p=['+pos.substr(0, pos.length-1)+']&t='+d.getTime();
				pos = '';
				posCount = 0;
			}
		} catch (oops) {};
	};

	/*
	 * Make the init function global
	 */
	window['bCT'] = {
		init: init
	};
})();
