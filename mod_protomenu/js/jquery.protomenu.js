
/**
 * @package        HEAD. Protomenü 2
 * @version        3.1.0
 * 
 * @author         Carsten Ruppert <webmaster@headmarketing.de>
 * @link           https://www.headmarketing.de
 * @copyright      Copyright © 2018 HEAD. MARKETING GmbH All Rights Reserved
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

'use strict';
(function($) {

	$.Protomenu = function( options, node )
	{
		this.$node = $(node); // <nav/div class="ptmenu">
		this.$node.data('protomenu', this);
		this._init( options );
	};

	$.Protomenu.defaults = {
		class : {
			open 		: 'open',	// Geöffnete Untermenüs
			expanded 	: 'expanded', // Untermenüs in denen Untermenüs geöffnet sind
			rtl 		: 'rtl', 	// Autoalign „right to left”
			btt 		: 'btt'		// Autoalign „bottom to top”
		},
		autoalign 		: true,
		seperateswitch 	: false, 	// Hier auf true setzen, wenn Umschalter und Anker getrennt werden sollen.
		mouseover 		: false, 	// Öffnen von Untermenüs bei Mouseover
		clickAnywhere 	: false,	// Irgendwo - außerhalb des Menüs - klicken um alle Menüs zu schließen?
		plugins 		: []
	};

	$.Protomenu.Plugins = [];

	$.Protomenu.prototype = {

		_init : function( options )
		{
			this.opt 		= $.extend({}, $.Protomenu.defaults, options);
			this.$wrapper 	= this.$node.children('.nav-wrapper');

			this.setup();
			this.initPlugins();
		},

		/*
			Menü einrichten
		*/
		setup : function()
		{	
			const submenus = this.$node.find('[data-ptm-child]');
			
			for(let i = 0, ilen = submenus.length; i < ilen; i++)
			{
				let sub = submenus.eq(i),
					triggers;

				if(!this.opt.mouseover) 
				{
					triggers = this.$node.find('[data-ptm-trigger="' + sub.data('ptm-child') + '"]');
				}
				else 
				{
					triggers = this.$node.find('[data-ptm-item="' + sub.data('ptm-child') + '"]').not('[data-ptm-item].static');
				}

				for(let x = 0, xlen = triggers.length; x < xlen; x++)
				{
					let trigger = triggers.eq(x),
						d  		= {submenu : sub},
						sep 	= trigger.find('[data-ptm-switcher]');

					trigger = sep.length && this.opt.seperateswitch ? sep : trigger;
					trigger.data('ptmenu', d);

					this.attachTriggerEvent(trigger);
				}

				sub.data('ptmenu', {triggers : triggers});
			}

			// Bei nicht Mouseover irgendwo im Dokument (außerhalb vom Menü) klicken, um Alles zu schließen.
			if(this.opt.clickAnywhere) 
			{
				$(document).on('click.protomenu', function(ev)
				{ 
					if(!$.contains(this.$node.get(0), ev.target))
					{
						this.closeRootLevel();
					}
				}.bind(this));
			}

			this.$node.on('afterStateChanged.protomenu', function() 
			{
				if(this.isExpanded())
				{
					this.$node.addClass(this.opt.class.expanded);
				}
				else 
				{
					this.$node.removeClass(this.opt.class.expanded);
				}
			}.bind(this));

		},

		initPlugins : function()
		{
			this.opt.plugins = this.opt.plugins.concat($.Protomenu.Plugins);
			this.opt.plugins = this.opt.plugins.filter(function(value, index, self){return self.indexOf(value) === index;}); // https://stackoverflow.com/questions/1960473/get-all-unique-values-in-an-array-remove-duplicates

			if( this.opt.plugins.length > 0 )
			{
				for( var i = 0, len = this.opt.plugins.length; i < len; i++ )
				{
					this[this.opt.plugins[i]] = new $[this.opt.plugins[i]](this); // = z.B.: this.ProtomenuBackdrop = new $.ProtomenuBackdrop(this);
				}
			}
		},


		/*
			Auslösereignisse an Auslöser binden (Anker oder separater „Umschalter”).
		*/
		attachTriggerEvent : function(trigger)
		{
			const _ = this;
			/*
				Bei Maussteuerung ist der „trigger” ein <li> (und zwar ALLE <li> im Menü).
				Bei Klicksteuerung ist der trigger ein beliebiges Element mit dem Attribut data-ptm-trigger
			*/
			if(this.opt.mouseover)
			{	
				// Maus Events
				trigger.on('mouseenter.protomenu mouseleave.protomenu', function(ev)
				{
					let item = $(this);
					_.toggleSubmenu(ev, item);
				});

				// Touch Event
				trigger.on('touchend.protomenu', function(ev) 
				{
					let item = $(this);

					if(item.data('ptmenu') && ev.delegateTarget === this)
					{
						ev.preventDefault();
						ev.stopPropagation();
					}
					_.toggleSubmenu(ev, item);
				});
			}
			else 
			{
				trigger.on('click.protomenu', function(ev)
				{
					let item = $(this);

					if(item.data('ptmenu'))
					{
						ev.preventDefault();
						ev.stopPropagation();
					}
					_.toggleSubmenu(ev, item);
				});
			}
		},

		/*
		pauseEvents : function(el, events)
		{
			if(!el.data('paused-events')) 
			{
				el.data('paused-events', {});
			}

			events.forEach(function(name)
			{
				el.data('paused-events')[name] = el.data('events')[name];
				el.data('events')[name] = null;
			}, this);
		},

		unpauseEvents : function(el, events)
		{
			if(!el.data('paused-events')) return;

			events.forEach(function(name)
			{
				el.data('events')[name] = el.data('paused-events')[name];
				el.data('paused-events')[name] = null;
			}, this);
		},
		*/

		getVps : function() 
		{
			let w = window,
				e = document.documentElement,
				b = document.getElementsByTagName('body')[0],
				x = w.innerWidth || e.clientWidth || b.clientWidth,
				y = w.innerHeight|| e.clientHeight|| b.clientHeight;

			return {w : x, h : y};
		},

		/*
			Deaktiviert alle „Auslöser” eines „Untermenüs”.
		*/
		disableTriggersOf : function(sub)
		{
			sub.data('ptmenu').triggers.removeClass(this.opt.class.open);

			const descestors = sub.find('[data-ptm-child]');
			
			for(let i = 0, len = descestors.length; i < len; i++)
			{
				let data = descestors.eq(i).data('ptmenu');

				if(data)
				{
					data.triggers.removeClass(this.opt.class.open); // Auslöser aller Nachkommen deaktivieren
				}
			}
		},

		/*
			Schließe alle Nachkommen von Untermenü „sub”.
		*/
		closeDescestorsOf : function(sub)
		{
			if(this.opt.mouseover)
			{
				const descestors = sub.find('[data-ptm-child]');
				descestors.removeClass(this.opt.class.open);
			}
			else 
			{
				const triggers = sub.find('[data-ptm-trigger]').not('.nav-child-header [data-ptm-trigger]');

				for(let i = 0; i < triggers.length; i++)
				{
					let decestor = this.$node.find('[data-ptm-child="' + triggers.eq(i).data('ptm-trigger') + '"]');
					if(decestor.length) 
					{
						decestor.removeClass(this.opt.class.open);
						this.closeDescestorsOf(decestor);
					}
				}
			}
		},

		/*
			Sucht und schließt offene Elemente in Ebene 2, die erste die aufklappen kann.
			Nachkommen von Ebene 2, die geöffnet sind, werden automatisch geschlossen.
		*/
		closeRootLevel : function()
		{
			const sub = this.$node.find('.' + this.opt.class.open + '[data-ptm-child][data-ptm-level="2"]');

			if(sub.length)
			{
				this.hideSub(sub); //.eq(0));
			}
		},

		/*
			Schließt alle anderen Untermenüs auf der gleichen Ebene wie „sub”.
		*/
		closeEqualLevel : function(sub)
		{
			//const subs = sub.parents('ul').eq(0).find('.' + this.opt.class.open + '[data-ptm-child]').not(sub);
			const subs = this.$node.find('[data-ptm-level="' + sub.data('ptm-level') + '"]').not(sub);

			for(let i = 0, len = subs.length; i < len; i++ )
			{
				this.hideSub(subs.eq(i));
			}
		},

		toggleSubmenu : function(ev, trigger)
		{	
			if(!trigger.data('ptmenu')) return; // trigger kam mit Mouseenter oder -leave hier rein, und hat kein Untermenü.

			const data		= trigger.data('ptmenu'),
				  isopen 	= data.submenu.hasClass(this.opt.class.open);

			switch(ev.type) 
			{
				case 'mouseenter' :
					this.showSub(data.submenu);
				break;

				case 'mouseleave' :
					this.hideSub(data.submenu);
				break;

				default : // click und touchend
					if(isopen)
					{
						this.hideSub(data.submenu);
					}
					else
					{
						this.showSub(data.submenu);
					}
			}
		},

		/*
			Macht ein „Untermenü”, und dessen „Nachkommen”, zu.
		*/
		hideSub : function(sub)
		{
			sub.removeClass(this.opt.class.open);
			this.closeDescestorsOf(sub);
			this.disableTriggersOf(sub);

			let parents = sub.parents('[data-ptm-child]');
			if(parents.length)
			{
				parents.eq(parents.length -1).removeClass(this.opt.class.expanded);
			}

			this.$node.triggerHandler('afterStateChanged', {closed : sub});
		},

		/*
			Macht ein „Untermenü” auf
		*/
		showSub : function(sub)
		{
			const data = sub.data('ptmenu');

			this.closeEqualLevel(sub);

			// Autoalign von rechts nach links
			if(this.opt.autoalign)
			{
				if(this.getVps().w - (sub.offset().left + sub.outerWidth()) < 0) 
				{
					sub.addClass(this.opt.class.rtl);
				}
			}

			sub.addClass(this.opt.class.open);
			data.triggers.addClass(this.opt.class.open);

			let parents = sub.parents('[data-ptm-child]');
			if(parents.length)
			{
				parents.eq(parents.length -1).addClass(this.opt.class.expanded);
			}

			this.$node.triggerHandler('afterStateChanged', {opened : sub});
		},

		/*
			Ist noch irgenein sub offen?
		*/
		isExpanded : function(first)
		{
			let something;
			if(first) // Nur im first-level suchen
			{
				something = this.$node.find('.' + this.opt.class.open + '[data-ptm-child][data-ptm-level="2"]');
			}
			else
			{
				something = this.$node.find('.' + this.opt.class.open + '[data-ptm-child]');
			}

			if(something.length)
			{
				return something.length;
			}

			return false;
		}

	} // prototype

	$.fn.protomenu = function(options)
	{
		let self = $(this).data('protomenu');

		if( self === undefined )
		{
			self = new $.Protomenu(options, this);
		}
		return self;
	}

})(jQuery);


(function($) {

	//$.Protomenu.Plugins.push('ProtomenuBackdrop');

	$.Protomenu.defaults.backDrop = {
		template : '<div class="ptmenu-backdrop"></div>'
	};

	$.ProtomenuBackdrop = function(parent) {
		this.parent = parent;
		this._init();
	}

	$.ProtomenuBackdrop.prototype = {
		_init : function()
		{
			this.backdrop 	= $(this.parent.opt.backDrop.template);
			this.timer 		= null;

			$('body').prepend(this.backdrop);

			this.parent.$node.on('afterStateChanged', function(ev)
			{
				if(this.parent.isExpanded(true))
				{
					this.backdrop.addClass(this.opt.class.open);
				}
				else
				{
					this.close();
				}
			}.bind(this));
		},

		close : function()
		{
			window.clearTimeout(this.timer);

			var time = 0;
			this.backdrop.css('transition-duration').split(',').forEach(function(dur)
			{
				dur = parseFloat(dur);
				time = dur > time ? dur : time;
			});

			var afterTransition = function()
			{
				if(this.parent.isExpanded()) return;
			};

			this.timer = window.setTimeout(
				afterTransition.bind(this),
				time * 1000
			);

			this.backdrop.removeClass(this.parent.opt.class.open);
		}
	}


	// $.Protomenu.Plugins.push('ProtomenuHtml5Video');

	$.Protomenu.defaults.html5video = {
		autoplay : true
	};

	$.ProtomenuHtml5Video = function(parent) {
		this.parent = parent;
		this._init();
	}

	$.ProtomenuHtml5Video.prototype = {
		_init : function()
		{
			this.parent.$node.on('afterStateChanged', function(ev, data) {
				this.tick(data);
			}.bind(this));
		},

		tick : function(data)
		{
			if(data.closed) {
				data.closed.find('video').each(function() {
					this.pause();
					if(this.currentTime) { // Wenn IE11 noch keine Metadaten geladen hat bekommen wir sonst beim ersten abfeuern von hideVideo einen InvalidStateError:
						this.currentTime = 0;
					}
				});
			}

			if(data.opened) {
				var videos = data.opened.find('video');

				var vid;
				for(var i = 0, len = videos.length; i < len; i++) {
					vid = videos.get(i);
					vid.offsetWidth;
					if(vid.readyState >=2) {
						this.playVideo(vid);
					}
					else {
						$(vid).one('canplay.protomenu.html5video', function(ev) {
							this.playVideo(ev.originalTarget);
						}.bind(this));
					}
				}
			}
		},

		playVideo : function(video)
		{
			if(video)
			{
				var opt = $(video).data('ptoptions') || {};
				// Autoplay
				if(this.parent.opt.html5video.autoplay || opt.autoplay)
				{
					video.play();
				}
			}
		}
	}
})(jQuery);