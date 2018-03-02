/**
	Protomenu 2.0.1
	Carsten Ruppert - 2018-03-01

	2.0.1 - 2018-03-01
	- Fix für MouseOver: Untermenus bleiben geschlossen, obwohl Mauszeiger auf einem Elterneintrag zeigt.

	2.0.0 - 2018-02-23
	- Plugins können nun auch dynamisch pro Instant geladen werden, nicht mehr nur für alle Instanzen
	- Fix von statischen CSS-Klassennamen im Code
	- Doppeltes auslösen von Event afterStateChanged korrigiert

	0.12.0 - 2018-02-22
	- Fix der Plugin-Logik. Es wurde immer ein und die gleiche Instanz eines Plugin-Objekts (Backdrop) benutzt, egal wieviele Instanzen von Protomenu existierten.
	- Fix von Backdrop-Plugin

	0.11.0 – 2017-11-29
	- Unterscheidung Touch und Mouseover, wenn Option „Mouseover” eingeschaltet ist.
	- etc.

	0.10.0 – 2017-11-06
	- Entkernt!
	- Abhängigkeit von $.prepareTransition entfernt
	- Abhängigkeit von $.touchSwipe entfernt

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
		classNames : {
			open 	: 'open',
			in 		: 'in'
		},
		seperateswitch 	: false, 	// Hier auf true setzen, wenn Umschalter und Anke getrennt werden sollen.
		mouseover 		: false, 	// Öffnen von Untermenüs bei Mouseover
		clickAnywhere 	: false,	// Irgendwo klicken um alle Menüs zu schließen? (Außer in dem Menü selbst, oder einem Modul in einem Menü)
		plugins 		: []
	};

	$.Protomenu.Plugins = [];

	$.Protomenu.prototype = {
		_init : function( options )
		{

			this.opt = $.extend({}, $.Protomenu.defaults, options);
			this.$menu  = this.$node.find('.nav-first');

			this.setupTriggers();
			this.setupEvents();

			// Plugins initialisieren
			/*
				Mit dem Käse wird immer auf das gleiche Plugin-Objekt zugegriffen.

			if( $.Protomenu.Plugins.length > 0 )
			{
				for( var i = 0, len = $.Protomenu.Plugins.length; i < len; i++ )
				{
					this[$.Protomenu.Plugins[i]].parent = this;
					this[$.Protomenu.Plugins[i]]._init();
				}
			}*/

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
			Auslösereignis an Auslöser für Untermenüs „el” binden (Anker oder separater „Umschalter”).

			el – jQuery - Ein <a class="nav-item"> oder ein <xyz class="item-switch"> unterhalb von <a ...
		*/
		attachTriggerEvent : function(el)
		{
			let self = this;

			if(this.opt.mouseover)
			{
				el.on('mouseover.protomenu', function(ev)
				{
					let item = $(this);

					if(item.data('ptmenu'))
					{
						ev.stopPropagation();
					}
					self.toggleSubmenu(item, true);
				});

				el.on('touchend.protomenu', function(ev){
					let item = $(this);

					if(item.data('ptmenu'))
					{
						ev.preventDefault();
						ev.stopPropagation();
					}
					self.toggleSubmenu(item);
				});
			}
			else {
				el.on('click.protomenu', function(ev)
				{
					let item = $(this);

					if(item.data('ptmenu'))
					{
						ev.preventDefault();
						ev.stopPropagation();
					}
					self.toggleSubmenu(item);
				});
			}
		},

		/*
			Sonstige Events einrichten.
		*/
		setupEvents : function() {
			let self = this;

			if( this.opt.clickAnywhere )
			{
				$(document).on('click', function(ev)
				{ // Klick irgendwo zu schließen
					self.closeRootLevel();
				});
			}
			// -- Bubbling stoppen:
			this.$node.find('.nav-module, .nav-child-toolbar, .nav-item, .nav-container-toolbar').on('click.protomenu', function(ev)
			{ // Verhindern, dass ein klick auf den Content eines Moduls im Menü den Click-Event am Body auslöst.
				ev.stopPropagation();
			});
		},

		/*
			Auslöser einrichten
		*/
		setupTriggers : function()
		{
			let self = this;

			this.$submenus = this.$menu.find('.nav-container, .nav-child').not('.mega .nav-child .nav-child');
			for(let i = 0, ilen = this.$submenus.length; i < ilen; i++)
			{
				let sub 		= this.$submenus.eq(i),
					triggers 	= this.$menu.find('.trigger-' + sub.attr('id'));

				for(let x = 0, xlen = triggers.length; x < xlen; x++)
				{
					let trigger = triggers.eq(x),
						d  		= {submenu : sub},
						sep 	= trigger.children('.item-switch');

					trigger = sep.length && this.opt.seperateswitch ? sep : trigger;
					trigger.data('ptmenu', d);
				}

				let d = {triggers : triggers};
				sub.data('ptmenu', d);
			}

			this.items = this.$menu.find('.nav-item');
			for(let i = 0; i < this.items.length; i++)
			{
				let trigger = this.items.eq(i),
					sep 	= trigger.children('.item-switch');

				this.attachTriggerEvent( (sep.length && this.opt.seperateswitch ? sep : trigger) );
			}

		},


		/*
			Deaktiviert alle „Auslöser” eines „Untermenüs”.
		*/
		disableTriggers : function(sub)
		{
			sub.data('ptmenu').triggers.removeClass(this.opt.classNames.open);

			let descestors = sub.find('.nav-child');
			for(let i = 0, len = descestors.length; i < len; i++)
			{
				let d = descestors.eq(i).data('ptmenu');
				if(d) d.triggers.removeClass(this.opt.classNames.open); // Auslöser aller Nachkommen deaktivieren
			}
		},

		/*
			Schließe alle „Nachkommen” eines „Untermenüs”.
		*/
		disableDescestors : function(sub)
		{
			let descestors = sub.find('.nav-child');
			descestors.removeClass( $.map(this.opt.classNames, function(n){return n}).join(' ') );
		},

		/*
			Versteckt ein „Untermenü” nachdem dessen Transition beendet ist – sofern vorhanden
		*/
		disableAfterTransition : function(sub)
		{
			sub.removeClass(this.opt.classNames.in);
			this.disableDescestors(sub);
		},

		/*
			Macht ein „Untermenü”, und dessen „Nachkommen”, zu.
		*/
		hideSub : function(sub)
		{
			/*
				let tdur = parseFloat(sub.css('transition-duration'));
				Siehe unten Problem „transitionEnd”
			*/

			let tdur = sub.css('transition-duration').split(','), // „transitionDuration”
				d 	 = sub.data('ptmenu'),
				time = 0;

			tdur.forEach(function(dur) { // Dauer von längster transition ermitteln
				dur = parseFloat(dur);
				time = dur > time ? dur : time;
			});

			if(time > 0) // Es wird ein transition benutzt
			{
				let self = this;
				/*
					Das Problem mit transitionEnd:
					- z.B.:
						.nav-child{
							transition: transform 0.3s ease, opacity 0.5 linear;
						}

						sub.one(...) würde nach 0.3s feuern, nicht nach 0.5 – Der CSS Author müsste zwingend die längste duration als erstes eingeben
						sub.on() würde 2mal feuern, weil jede transition-Property den Event auslöst

				sub.one('transitionend transitionEnd', function(ev)
				{
					let sub = $(this);
					sub.removeClass('in');
					self.disableDescestors(sub);
				});

				Deshalb setTimeout:
				*/
				/*
				let afterTransition = function(sub)
				{
					sub.removeClass(this.opt.classNames.in);
					this.disableDescestors(sub);
				};

				d.timeout = window.setTimeout(
								afterTransition.bind(this, sub),
								time * 1000
							);
				*/
				d.timeout = window.setTimeout(
								this.disableAfterTransition.bind(this, sub),
								time * 1000
							);

				sub.data('ptmenu', d);
				sub.removeClass(this.opt.classNames.open);
			}
			else // Es wird kein transition benutzt
			{
				sub.removeClass( $.map(this.opt.classNames, function(n){return n}).join(' ') );
				this.disableDescestors(sub);
			}
			this.disableTriggers(sub);

			this.$node.triggerHandler('afterStateChanged');
		},

		/*
			Macht ein „Untermenü” auf
		*/
		showSub : function(sub)
		{
			let d = sub.data('ptmenu');

			if(d.timeout && this.opt.mouseover) window.clearTimeout(d.timeout); // Sofern für dieses „sub” ein Timeout in closeSub gesetzt wurde, müssen wir diesen hier löschen.

			this.closeEqualLevel(sub);

			sub.addClass( $.map(this.opt.classNames, function(n){return n}).join(' ') );
			d.triggers.addClass(this.opt.classNames.open);

			this.$node.triggerHandler('afterStateChanged');
		},

		/*
			Sucht und schließt offene Elemente im Root-Level (und alle darunter). Wird bei Mouseover true benutzt.
		*/
		closeRootLevel : function()
		{
			let sub = this.$menu.find('.nav-child.nav-level-2.' + this.opt.classNames.open);

			if(sub.length) this.hideSub(sub);
		},

		/*
			Mach Alles auf der gleichen Ebene zu.
		*/
		closeEqualLevel : function(el)
		{
			let subs = el.parent().parent().find('.nav-child.'+this.opt.classNames.open).not(el);

			for(let i = 0, len = subs.length; i < len; i++ )
			{
				this.hideSub(subs.eq(i));
			}
		},

		/*
			Ist noch irgenein sub offen?
		*/
		isExpanded : function(first)
		{
			let something;
			if(first) // Nur im first-level suchen
			{
				something = this.$menu.find('.nav-child.nav-level-2.'+this.opt.classNames.open);
			}
			else
			{
				something = this.$menu.find('.nav-child.'+this.opt.classNames.open);
			}

			if(something.length)
				return true;

			return false;
		},

		toggleSubmenu : function(trigger, mouseover)
		{
			if(!trigger.data('ptmenu')) // Dieser „Trigger” hat kein Untermenü, wenn "this.opt.mouseover" an ist, könnte aber ein „Untermenü” offen sein, welches ausgeblendet werden muss.
			{
				if(mouseover) this.closeEqualLevel(trigger);
				return;
			}

			let sub 	= trigger.data('ptmenu').submenu,
				isopen 	= sub.hasClass(this.opt.classNames.open);

			if(isopen && !mouseover)
			{
				this.hideSub(sub);
				trigger.blur();
			}
			else {
				this.showSub(sub);
			}

			if( mouseover )
			{
				let self = this;

				this.$menu.children('li').off('mousemove.protomenu').on('mousemove.protomenu', function(ev){
					if( ! $(this).find('#'+sub.attr('id')).length )
					{
						$(document).off('mousemove.protomenu');
						self.closeRootLevel();
					}
				});

				$(document).off('mousemove.protomenu').on('mousemove.protomenu', function(ev){
					if( ! $(ev.target).parents('.ptmenu').length )
					{
						$(document).off('mousemove.protomenu');
						self.closeRootLevel();
					}
				})
			}
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

	$.ProtomenuBackdrop = function(parent) {
		this.parent = parent;
		this._init();
	}

	$.ProtomenuBackdrop.prototype = {
		_init : function()
		{
			let self = this;

			this.backdrop = $(this.parent.opt.backDrop.template);
			this.timer = null;

			$('body').prepend(this.backdrop);

			this.parent.$node.on('afterStateChanged', function(ev)
			{
				if(self.parent.isExpanded(true))
				{
					// self.backdrop.addClass('open in');
					self.backdrop.addClass( $.map(self.parent.opt.classNames, function(n){return n}).join(' ') );
				}
				else
				{
					self.close();
				}
			});

			/*
			this.parent.$node.on('closeRoot', function(ev) // Nur Mouseover triggert diesen Event
			{
				self.close();
			});
			*/
		},

		close : function()
		{
			window.clearTimeout(this.timer);

			let time = 0;

			this.backdrop.css('transition-duration').split(',').forEach(function(dur)
			{
				dur = parseFloat(dur);
				time = dur > time ? dur : time;
			});

			let afterTransition = function()
			{
				if(this.parent.isExpanded()) return;
				//this.backdrop.removeClass('in');
				this.backdrop.removeClass(this.parent.opt.classNames.in);
			};

			this.timer = window.setTimeout(
				afterTransition.bind(this),
				time * 1000
			);
			//this.backdrop.removeClass('open');
			this.backdrop.removeClass(this.parent.opt.classNames.open);
		}
	}

})(jQuery);
