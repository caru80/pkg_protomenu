/**
	Protomenu 0.11.0
	Carsten Ruppert - 2017-11-29

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
		classopen		: 'open', 	// CSS Klasse geöffneter Untermenüs und aktiver Anker.
		seperateswitch 	: false, 	// Hier auf true setzen, wenn Umschalter und Anke getrennt werden sollen.
		mouseover 		: false, 	// Öffnen von Untermenüs bei Mouseover
		clickAnywhere 	: false		// Irgendwo klicken um alle Menüs zu schließen? (Außer in dem Menü selbst, oder einem Modul in einem Menü)
	};

	$.Protomenu.prototype = {
		_init : function( options ) {

			this.opt = $.extend({}, $.Protomenu.defaults, options);
			this.$menu  = this.$node.find('.nav-first');

			this.setupTriggers();
			this.setupEvents();
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
					self.closeRoot();
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
			Deaktiviert alle Auslöser eines „Untermenüs”.
		*/
		disableTriggers : function(sub)
		{
			sub.data('ptmenu').triggers.removeClass('open');

			let descestors = sub.find('.nav-child');
			for(let i = 0, len = descestors.length; i < len; i++)
			{
				descestors.eq(i).data('ptmenu').triggers.removeClass('open'); // Auslöser aller Nachkommen deaktivieren
			}
		},

		/*
			Schließe alle „Nachkommen” eines „Untermenüs”.
		*/
		disableDescestors : function(sub)
		{
			let descestors = sub.find('.nav-child');

			descestors.removeClass('open in');

			for(let i = 0, len = descestors.length; i < len; i++)
			{
				this.disableTriggers(descestors.eq(i));
			}
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

			let tdur = sub.css('transition-duration').split(','),
				time = 0;

			tdur.forEach(function(dur){
				dur = parseFloat(dur);
				time = dur > time ? dur : time;
			});

			if(time > 0)
			{
				let self = this;

				let afterTransition = function(sub){
					sub.removeClass('in');
					this.disableDescestors(sub);
				};
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
				window.setTimeout(
					afterTransition.bind(this, sub),
					time * 1000
				);
				sub.removeClass('open');
			}
			else {
				sub.removeClass('open in');
				this.disableDescestors(sub);
			}

			this.disableTriggers(sub);
		},

		/*
			Macht ein „Untermenü” auf
		*/
		showSub : function(sub)
		{
			let self = this;

			this.closeEqual(sub);

			sub.addClass('open in');
			sub.data('ptmenu').triggers.addClass('open');
		},

		/*
			Sucht und schließt offene Elemente im Root-Level.
		*/
		closeRoot : function() {
			let sub = this.$menu.find('.nav-child.nav-level-2.open');
			if(sub.length)
			{
				this.hideSub(sub);
			}
		},

		/*
			Mach Alles auf der gleichen Ebene zu.
		*/
		closeEqual : function(el)
		{
			let subs = el.parent().parent().find('.nav-child.open').not(el);

			for(let i = 0, len = subs.length; i < len; i++ )
			{
				this.hideSub(subs.eq(i));
			}
		},


		toggleSubmenu : function(trigger, mouseover)
		{
			if(!trigger.data('ptmenu')) // Dieser „Trigger” hat kein Untermenü, wenn "this.opt.mouseover" an ist, könnte aber ein „Untermenü” offen sein, welches ausgeblendet werden muss.
			{
				if(mouseover) this.closeEqual(trigger);
				return;
			}

			let sub 	= trigger.data('ptmenu').submenu,
				isopen 	= sub.hasClass(this.opt.classopen);

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
						self.closeRoot();
					}
				});

				$(document).off('mousemove.protomenu').on('mousemove.protomenu', function(ev){
					if( ! $(ev.target).parents('.ptmenu').length )
					{
						$(document).off('mousemove.protomenu');
						self.closeRoot();
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
