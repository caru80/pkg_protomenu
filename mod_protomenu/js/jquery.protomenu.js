
/**
 * @package        HEAD. Protomenü 2
 * @version        4.0
 * 
 * @author         Carsten Ruppert <webmaster@headmarketing.de>
 * @link           https://www.headmarketing.de
 * @copyright      Copyright © 2018 - 2019 HEAD. MARKETING GmbH All Rights Reserved
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
		plugins 		: [],
		events          : {
			//mouse : 'mouseenter.protomenu mouseleave.protomenu',
			//mouseDelay : 0,
			mouse : 'mouseenter.protomenu',
			mouseDelay : 200,
			touch : 'touchend.protomenu',
			click : 'click.protomenu'
		}
	};

	$.Protomenu.Plugins = [];

	$.Protomenu.prototype = {

		_init : function( options )
		{
			this.opt 		= $.extend(true, {}, $.Protomenu.defaults, options);
			this.$wrapper 	= this.$node.children('.nav-wrapper');

			this.childMenus 	= this.$node.find('.ptmenu'); 				// Protomenü in Protomenü
			this.childSubmenus 	= this.childMenus.find('[data-ptm-child]'); // Untermenüs von Protomenü in Protomenü

			this.setup();
			this.initPlugins();
		},

		/*
			Menü einrichten
		*/
		setup : function()
		{	
			const submenus = this.$node.find('[data-ptm-child]').not(this.childSubmenus);

			for(let i = 0, ilen = submenus.length; i < ilen; i++)
			{
				let sub 		= submenus.eq(i),
					triggers 	= this.$node.find('[data-ptm-item="' + sub.data('ptm-child') + '"]').not('[data-ptm-item].static');

				for(let x = 0, xlen = triggers.length; x < xlen; x++)
				{
					let trigger = triggers.eq(x),
						data	= {submenu : sub},
						sep 	= trigger.find('[data-ptm-switcher]');

					trigger = sep.length && this.opt.seperateswitch ? sep : trigger;
					trigger.data('ptmenu', data);

					this.attachTriggerEvent(trigger);
				}

				sub.data('ptmenu', {triggers : triggers});
			}

			// Bei nicht Mouseover irgendwo im Dokument (außerhalb vom Menü) klicken, um Alles zu schließen.
			if(this.opt.clickAnywhere) 
			{
				$(document).on('click.protomenu', function(ev)
				{ 
					if($.contains(this.$node.get(0), ev.target) === false)
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

			if(this.opt.mouseover)
			{	
				trigger.on(this.opt.events.mouse, function(ev)
				{
					let item 		= $(this),
						obsEvents 	= ['mouseenter','mouseover'];

					if(_.opt.events.mouseDelay > 0 && !!~obsEvents.indexOf(ev.type))
					{
						item.one('mouseleave.ildetimer', function()
						{
							window.clearTimeout(this._ptmenuIdleTimer);
						});

						item.get(0)._ptmenuIdleTimer = window.setTimeout(function(item, ev)
						{
							this.toggleSubmenu(ev, item);
							item.off('.ildetimer');
						}.bind(_, item, ev), _.opt.events.mouseDelay);
					}
					else
					{
						_.toggleSubmenu(ev, item);
					}
				});

				// Touch Event
				trigger.on(this.opt.events.touch, function(ev) 
				{
					let item = $(this);

					//if(item.data('ptmenu') && ev.delegateTarget === this) // Das ist falsch, weil delegateTarget ist bei <li> in einer <ul> IMMER this, weil nur noch items die ein Submenu haben einen Event auslösen.
					if($(ev.target).parents('[data-ptm-item]').get(0) === this)
					{
						ev.preventDefault();
						ev.stopPropagation();
					}
					else {
						/*
							Das Ereignis bricht sonst nicht ab und items, die kein Untermenü öffnen, lösen keinen Navigationsvorgang aus!
						*/
						return;
					}
					_.toggleSubmenu(ev, item);
				});
			}
			else 
			{
				trigger.on(this.opt.events.click, function(ev)
				{
					let item = $(this);

					//if(item.data('ptmenu'))
					if($(ev.target).parents('[data-ptm-item]').get(0) === this)
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
			Deaktiviert alle „Auslöser” eines „Untermenüs”, und die Auslöser dessen Kind-Menüs.
		*/
		disableTriggers : function(sub)
		{
			let triggers = sub.data('ptmenu').triggers;
			triggers.removeClass(this.opt.class.open);
		},
		
		/*
			Schließe alle Nachkommen von Untermenü „sub”.
		*/
		closeDescestors : function(sub)
		{
			const triggers = sub.find('[data-ptm-item].' + this.opt.class.open).not(this.childMenus.find('[data-ptm-item]')).not('.nav-child-header [data-ptm-item]');

			for (let i = 0, len = triggers.length; i < len; i++)
			{
				let decestor = this.$node.find('[data-ptm-child="' + triggers.eq(i).data('ptm-item') + '"].' + this.opt.class.open);

				if(decestor.length) 
				{
					decestor.removeClass(this.opt.class.open);
					this.disableTriggers(decestor);
				}
			}
		},

		/*
			Sucht und schließt offene Elemente in Ebene 2, die erste die aufklappen kann.
			Nachkommen von Ebene 2, die geöffnet sind, werden automatisch geschlossen.
		*/
		closeRootLevel : function()
		{
			const sub = this.$node.find('[data-ptm-child][data-ptm-level="2"].' + this.opt.class.open).not(this.childSubmenus);

			if(sub.length)
			{
				this.hideSub(sub);
			}
		},

		/*
			Schließt alle anderen Untermenüs auf der gleichen Ebene wie „sub”.
		*/
		closeEqualLevel : function(sub)
		{
			const subs = this.$node.find('[data-ptm-level="' + sub.data('ptm-level') + '"]').not(sub).not(this.childSubmenus);

			for(let i = 0, len = subs.length; i < len; i++ )
			{
				this.hideSub(subs.eq(i));
			}
		},

		toggleSubmenu : function(ev, trigger)
		{	
			if(!$(trigger).data('ptmenu')) return; // trigger kam mit Mouseenter oder -leave hier rein, und hat kein Untermenü.

			const data		= trigger.data('ptmenu'),
				  isopen 	= data.submenu.hasClass(this.opt.class.open);

			switch(ev.type) 
			{
				case 'mouseenter' :
				case 'mouseover' :
					this.showSub(data.submenu);
				break;

				case 'mouseleave' :
				case 'mouseout' :
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
			const parents = sub.parents('[data-ptm-child]');

			sub.removeClass(this.opt.class.open);
			this.closeDescestors(sub);
			this.disableTriggers(sub);
			
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
			const 	data 	= sub.data('ptmenu'),
					parents = sub.parents('[data-ptm-child]');

			this.closeEqualLevel(sub);

			sub.addClass(this.opt.class.open);
			data.triggers.addClass(this.opt.class.open);

			// Autoalign von rechts nach links
			if(this.opt.autoalign)
			{
				if(this.getVps().w - (sub.offset().left + sub.outerWidth()) < 0) 
				{
					sub.addClass(this.opt.class.rtl);
				}
			}
	
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
				something = this.$node.find('.' + this.opt.class.open + '[data-ptm-child][data-ptm-level="2"]').not(this.childSubmenus);
			}
			else
			{
				something = this.$node.find('.' + this.opt.class.open + '[data-ptm-child]').not(this.childSubmenus);
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
					this.backdrop.addClass(this.parent.opt.class.open);
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