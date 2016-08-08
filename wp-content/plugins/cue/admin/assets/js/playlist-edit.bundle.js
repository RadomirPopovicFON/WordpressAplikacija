require=(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
(function (global){
"use strict";
var Tracks,
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null),
	settings = require( 'cue' ).settings(),
	Track = require( '../models/track' ),
	wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null);

Tracks = Backbone.Collection.extend({
	model: Track,

	comparator: function( track ) {
		return parseInt( track.get( 'order' ), 10 );
	},

	fetch: function() {
		var collection = this;

		return wp.ajax.post( 'cue_get_playlist', {
			post_id: settings.postId
		}).done(function( tracks ) {
			collection.reset( tracks );
		});
	},

	save: function( data ) {
		this.sort();

		data = _.extend({}, data, {
			post_id: settings.postId,
			tracks: this.toJSON(),
			nonce: settings.saveNonce
		});

		return wp.ajax.post( 'cue_save_playlist_tracks', data );
	}
});

module.exports = Tracks;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"../models/track":2,"cue":"cue"}],2:[function(require,module,exports){
(function (global){
"use strict";
var Track,
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

Track = Backbone.Model.extend({
	defaults: {
		artist: '',
		artworkId: '',
		artworkUrl: '',
		audioId: '',
		audioUrl: '',
		format: '',
		length: '',
		title: '',
		order: 0
	}
});

module.exports = Track;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],3:[function(require,module,exports){
(function (global){
"use strict";
var Workflows,
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	cue = require( 'cue' ),
	l10n = require( 'cue' ).l10n,
	MediaFrame = require( '../views/media-frame' ),
	wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null),
	Attachment = wp.media.model.Attachment;

Workflows = {
	frames: [],
	model: {},

	/**
	 * Set a model for the current workflow.
	 *
	 * @param {Object} frame
	 */
	setModel: function( model ) {
		this.model = model;
		return this;
	},

	/**
	 * Retrieve or create a frame instance for a particular workflow.
	 *
	 * @param {string} id Frame identifer.
	 */
	get: function( id )  {
		var method = '_' + id,
			frame = this.frames[ method ] || null;

		// Always call the frame method to perform any routine set up. The
		// frame method should short-circuit before being initialized again.
		frame = this[ method ].call( this, frame );

		// Store the frame for future use.
		this.frames[ method ] = frame;

		return frame;
	},

	/**
	 * Workflow for adding tracks to the playlist.
	 *
	 * @param {Object} frame
	 */
	_addTracks: function( frame ) {
		// Return the existing frame for this workflow.
		if ( frame ) {
			return frame;
		}

		// Initialize the audio frame.
		frame = new MediaFrame({
			title: l10n.workflows.addTracks.frameTitle,
			library: {
				type: 'audio'
			},
			button: {
				text: l10n.workflows.addTracks.frameButtonText
			},
			multiple: 'add'
		});

		// Set the extensions that can be uploaded.
		frame.uploader.options.uploader.plupload = {
			filters: {
				mime_types: [{
					title: l10n.workflows.addTracks.fileTypes,
					extensions: 'm4a,mp3,ogg,wma'
				}]
			}
		};

		// Prevent the Embed controller scanner from changing the state.
		frame.state( 'embed' ).props.off( 'change:url', frame.state( 'embed' ).debouncedScan );

		// Insert each selected attachment as a new track model.
		frame.state( 'insert' ).on( 'insert', function( selection ) {
			_.each( selection.models, function( attachment ) {
				cue.tracks.push( attachment.toJSON().cue );
			});
		});

		// Insert the embed data as a new model.
		frame.state( 'embed' ).on( 'select', function() {

			var embed = this.props.toJSON(),
				track = {
					audioId: '',
					audioUrl: embed.url
				};

			if ( ( 'title' in embed ) && '' !== embed.title ) {
				track.title = embed.title;
			}

			cue.tracks.push( track );
		});

		return frame;
	},

	/**
	 * Workflow for selecting track artwork image.
	 *
	 * @param {Object} frame
	 */
	_selectArtwork: function( frame ) {
		var workflow = this;

		// Return existing frame for this workflow.
		if ( frame ) {
			return frame;
		}

		// Initialize the artwork frame.
		frame = wp.media({
			title: l10n.workflows.selectArtwork.frameTitle,
			library: {
				type: 'image'
			},
			button: {
				text: l10n.workflows.selectArtwork.frameButtonText
			},
			multiple: false
		});

		// Set the extensions that can be uploaded.
		frame.uploader.options.uploader.plupload = {
			filters: {
				mime_types: [{
					files: l10n.workflows.selectArtwork.fileTypes,
					extensions: 'jpg,jpeg,gif,png'
				}]
			}
		};

		// Automatically select the existing artwork if possible.
		frame.on( 'open', function() {
			var selection = this.get( 'library' ).get( 'selection' ),
				artworkId = workflow.model.get( 'artworkId' ),
				attachments = [];

			if ( artworkId ) {
				attachments.push( Attachment.get( artworkId ) );
				attachments[0].fetch();
			}

			selection.reset( attachments );
		});

		// Set the model's artwork ID and url properties.
		frame.state( 'library' ).on( 'select', function() {
			var attachment = this.get( 'selection' ).first().toJSON();

			workflow.model.set({
				artworkId: attachment.id,
				artworkUrl: attachment.sizes.cue.url
			});
		});

		return frame;
	},

	/**
	 * Workflow for selecting track audio.
	 *
	 * @param {Object} frame
	 */
	_selectAudio: function( frame ) {
		var workflow = this;

		// Return the existing frame for this workflow.
		if ( frame ) {
			return frame;
		}

		// Initialize the audio frame.
		frame = new MediaFrame({
			title: l10n.workflows.selectAudio.frameTitle,
			library: {
				type: 'audio'
			},
			button: {
				text: l10n.workflows.selectAudio.frameButtonText
			},
			multiple: false
		});

		// Set the extensions that can be uploaded.
		frame.uploader.options.uploader.plupload = {
			filters: {
				mime_types: [{
					title: l10n.workflows.selectAudio.fileTypes,
					extensions: 'm4a,mp3,ogg,wma'
				}]
			}
		};

		// Prevent the Embed controller scanner from changing the state.
		frame.state( 'embed' ).props.off( 'change:url', frame.state( 'embed' ).debouncedScan );

		// Set the frame state when opening it.
		frame.on( 'open', function() {
			var selection = this.get( 'insert' ).get( 'selection' ),
				audioId = workflow.model.get( 'audioId' ),
				audioUrl = workflow.model.get( 'audioUrl' ),
				isEmbed = audioUrl && ! audioId,
				attachments = [];

			// Automatically select the existing audio file if possible.
			if ( audioId ) {
				attachments.push( Attachment.get( audioId ) );
				attachments[0].fetch();
			}

			selection.reset( attachments );

			// Set the embed state properties.
			if ( isEmbed ) {
				this.get( 'embed' ).props.set({
					url: audioUrl,
					title: workflow.model.get( 'title' )
				});
			} else {
				this.get( 'embed' ).props.set({
					url: '',
					title: ''
				});
			}

			// Set the state to 'embed' if the model has an audio URL but
			// not a corresponding attachment ID.
			frame.setState( isEmbed ? 'embed' : 'insert' );
		});

		// Copy data from the selected attachment to the current model.
		frame.state( 'insert' ).on( 'insert', function( selection ) {
			var attachment = selection.first().toJSON().cue,
				data = {},
				keys = _.keys( workflow.model.attributes );

			// Attributes that shouldn't be updated when inserting an
			// audio attachment.
			_.without( keys, [ 'id', 'order' ] );

			// Update these attributes if they're empty.
			// They shouldn't overwrite any data entered by the user.
			_.each( keys, function( key ) {
				var value = workflow.model.get( key );

				if ( ! value && ( key in attachment ) && value !== attachment[ key ] ) {
					data[ key ] = attachment[ key ];
				}
			});

			// Attributes that should always be replaced.
			data.audioId  = attachment.audioId;
			data.audioUrl = attachment.audioUrl;

			workflow.model.set( data );
		});

		// Copy the embed data to the current model.
		frame.state( 'embed' ).on( 'select', function() {
			var embed = this.props.toJSON(),
				data = {};

			data.audioId  = '';
			data.audioUrl = embed.url;

			if ( ( 'title' in embed ) && '' !== embed.title ) {
				data.title = embed.title;
			}

			workflow.model.set( data );
		});

		// Remove an empty model if the frame is escaped.
		frame.on( 'escape', function() {
			var model = workflow.model.toJSON();

			if ( ! model.artworkUrl && ! model.audioUrl ) {
				workflow.model.destroy();
			}
		});

		return frame;
	}
};

module.exports = Workflows;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"../views/media-frame":6,"cue":"cue"}],4:[function(require,module,exports){
(function (global){
"use strict";
/*global _cueSettings:false */

var $ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	cue = require( 'cue' ),
	mejs = (typeof window !== "undefined" ? window['mejs'] : typeof global !== "undefined" ? global['mejs'] : null),
	wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null);

cue.data = _cueSettings; // Back-compat.
cue.settings( _cueSettings );

wp.media.view.settings.post.id = cue.data.postId;
wp.media.view.settings.defaultProps = {};

// Add mime-type aliases to MediaElement plugin support.
mejs.plugins.silverlight[0].types.push( 'audio/x-ms-wma' );

cue.model.Track = require( './models/track' );
cue.model.Tracks = require( './collections/tracks' );

cue.view.MediaFrame = require( './views/media-frame' );
cue.view.PostForm = require( './views/post-form' );
cue.view.AddTracksButton = require( './views/button/add-tracks' );
cue.view.TrackList = require( './views/track-list' );
cue.view.Track = require( './views/track' );
cue.view.TrackArtwork = require( './views/track/artwork' );
cue.view.TrackAudio = require( './views/track/audio' );

cue.workflows = require( './modules/workflows' );

/**
 * ========================================================================
 * SETUP
 * ========================================================================
 */

$(function( $ ) {
	var tracks;

	tracks = cue.tracks = new cue.model.Tracks( cue.data.tracks );
	delete cue.data.tracks;

	new cue.view.PostForm({
		collection: tracks,
		l10n: cue.l10n
	});
});

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./collections/tracks":1,"./models/track":2,"./modules/workflows":3,"./views/button/add-tracks":5,"./views/media-frame":6,"./views/post-form":7,"./views/track":9,"./views/track-list":8,"./views/track/artwork":10,"./views/track/audio":11,"cue":"cue"}],5:[function(require,module,exports){
(function (global){
"use strict";
var AddTracksButton,
	$ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	workflows = require( '../../modules/workflows' ),
	wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null);

AddTracksButton = wp.Backbone.View.extend({
	id: 'add-tracks',
	tagName: 'p',

	events: {
		'click .button': 'click'
	},

	initialize: function( options ) {
		this.l10n = options.l10n;
	},

	render: function() {
		var $button = $( '<a />', {
			text: this.l10n.addTracks
		}).addClass( 'button button-secondary' );

		this.$el.html( $button );

		return this;
	},

	click: function( e ) {
		e.preventDefault();
		workflows.get( 'addTracks' ).open();
	}
});

module.exports = AddTracksButton;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"../../modules/workflows":3}],6:[function(require,module,exports){
(function (global){
"use strict";
var MediaFrame,
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	l10n = require( 'cue' ).l10n,
	wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null);

MediaFrame = wp.media.view.MediaFrame.Post.extend({
	createStates: function() {
		var options = this.options;

		// Add the default states.
		this.states.add([
			// Main states.
			new wp.media.controller.Library({
				id: 'insert',
				title: this.options.title,
				priority: 20,
				toolbar: 'main-insert',
				filterable: 'uploaded',
				library: wp.media.query( options.library ),
				multiple: options.multiple ? 'reset' : false,
				editable: false,

				// If the user isn't allowed to edit fields,
				// can they still edit it locally?
				allowLocalEdits: true,

				// Show the attachment display settings.
				displaySettings: false,
				// Update user settings when users adjust the
				// attachment display settings.
				displayUserSettings: false
			}),

			// Embed states.
			new wp.media.controller.Embed({
				title: l10n.addFromUrl,
				menuItem: { text: l10n.addFromUrl, priority: 120 },
				type: 'link'
			})
		]);
	},

	bindHandlers: function() {
		wp.media.view.MediaFrame.Select.prototype.bindHandlers.apply( this, arguments );

		this.on( 'toolbar:create:main-insert', this.createToolbar, this );
		this.on( 'toolbar:create:main-embed', this.mainEmbedToolbar, this );

		var handlers = {
				menu: {
					'default': 'mainMenu'
				},

				content: {
					'embed': 'embedContent',
					'edit-selection': 'editSelectionContent'
				},

				toolbar: {
					'main-insert': 'mainInsertToolbar'
				}
			};

		_.each( handlers, function( regionHandlers, region ) {
			_.each( regionHandlers, function( callback, handler ) {
				this.on( region + ':render:' + handler, this[ callback ], this );
			}, this );
		}, this );
	},

	// Toolbars.
	mainInsertToolbar: function( view ) {
		var controller = this;

		this.selectionStatusToolbar( view );

		view.set( 'insert', {
			style: 'primary',
			priority: 80,
			text: controller.options.button.text,
			requires: {
				selection: true
			},
			click: function() {
				var state = controller.state(),
					selection = state.get( 'selection' );

				controller.close();
				state.trigger( 'insert', selection ).reset();
			}
		});
	},

	mainEmbedToolbar: function( toolbar ) {
		toolbar.view = new wp.media.view.Toolbar.Embed({
			controller: this,
			text: this.options.button.text
		});
	}
});

module.exports = MediaFrame;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"cue":"cue"}],7:[function(require,module,exports){
(function (global){
"use strict";
var PostForm,
	$ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	AddTracksButton = require( './button/add-tracks' ),
	TrackList = require( './track-list' ),
	wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null);

PostForm = wp.Backbone.View.extend({
	el: '#post',
	saved: false,

	events: {
		'click #publish': 'buttonClick',
		'click #save-post': 'buttonClick'
		//'submit': 'submit'
	},

	initialize: function( options ) {
		this.l10n = options.l10n;

		this.render();
	},

	render: function() {
		this.views.add( '#cue-playlist-editor .cue-panel-body', [
			new AddTracksButton({
				collection: this.collection,
				l10n: this.l10n
			}),

			new TrackList({
				collection: this.collection
			})
		]);

		return this;
	},

	buttonClick: function( e ) {
		var self = this,
			$button = $( e.target );

		if ( ! self.saved ) {
			this.collection.save().done(function( data ) {
				self.saved = true;
				$button.click();
			});
		}

		return self.saved;
	}
});

module.exports = PostForm;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./button/add-tracks":5,"./track-list":8}],8:[function(require,module,exports){
(function (global){
"use strict";
var TrackList,
	$ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	Track = require( './track' ),
	wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null);

TrackList = wp.Backbone.View.extend({
	className: 'cue-tracklist',
	tagName: 'ol',

	initialize: function() {
		this.listenTo( this.collection, 'add', this.addTrack );
		this.listenTo( this.collection, 'add remove', this.updateOrder );
		this.listenTo( this.collection, 'reset', this.render );
	},

	render: function() {
		this.$el.empty();

		this.collection.each( this.addTrack, this );
		this.updateOrder();

		this.$el.sortable( {
			axis: 'y',
			delay: 150,
			forceHelperSize: true,
			forcePlaceholderSize: true,
			opacity: 0.6,
			start: function( e, ui ) {
				ui.placeholder.css( 'visibility', 'visible' );
			},
			update: _.bind(function( e, ui ) {
				this.updateOrder();
			}, this )
		} );

		return this;
	},

	addTrack: function( track ) {
		var trackView = new Track({ model: track });
		this.$el.append( trackView.render().el );
	},

	updateOrder: function() {
		_.each( this.$el.find( '.cue-track' ), function( item, i ) {
			var cid = $( item ).data( 'cid' );
			this.collection.get( cid ).set( 'order', i );
		}, this );
	}
});

module.exports = TrackList;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./track":9}],9:[function(require,module,exports){
(function (global){
"use strict";
var Track,
	$ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	TrackArtwork = require( './track/artwork' ),
	TrackAudio = require( './track/audio' ),
	wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null);

Track = wp.Backbone.View.extend({
	tagName: 'li',
	className: 'cue-track',
	template: wp.template( 'cue-playlist-track' ),

	events: {
		'change [data-setting]': 'updateAttribute',
		'click .js-toggle': 'toggleOpenStatus',
		'dblclick .cue-track-title': 'toggleOpenStatus',
		'click .js-close': 'minimize',
		'click .js-remove': 'destroy'
	},

	initialize: function() {
		this.listenTo( this.model, 'change:title', this.updateTitle );
		this.listenTo( this.model, 'change', this.updateFields );
		this.listenTo( this.model, 'destroy', this.remove );
	},

	render: function() {
		this.$el.html( this.template( this.model.toJSON() ) ).data( 'cid', this.model.cid );

		this.views.add( '.cue-track-column-artwork', new TrackArtwork({
			model: this.model,
			parent: this
		}));

		this.views.add( '.cue-track-audio-group', new TrackAudio({
			model: this.model,
			parent: this
		}));

		return this;
	},

	minimize: function( e ) {
		e.preventDefault();
		this.$el.removeClass( 'is-open' ).find( 'input:focus' ).blur();
	},

	toggleOpenStatus: function( e ) {
		e.preventDefault();
		this.$el.toggleClass( 'is-open' ).find( 'input:focus' ).blur();

		// Trigger a resize so the media element will fill the container.
		if ( this.$el.hasClass( 'is-open' ) ) {
			$( window ).trigger( 'resize' );
		}
	},

	/**
	 * Update a model attribute when a field is changed.
	 *
	 * Fields with a 'data-setting="{{key}}"' attribute whose value
	 * corresponds to a model attribute will be automatically synced.
	 *
	 * @param {Object} e Event object.
	 */
	updateAttribute: function( e ) {
		var attribute = $( e.target ).data( 'setting' ),
			value = e.target.value;

		if ( this.model.get( attribute ) !== value ) {
			this.model.set( attribute, value );
		}
	},

	/**
	 * Update a setting field when a model's attribute is changed.
	 */
	updateFields: function() {
		var track = this.model.toJSON(),
			$settings = this.$el.find( '[data-setting]' ),
			attribute, value;

		// A change event shouldn't be triggered here, so it won't cause
		// the model attribute to be updated and get stuck in an
		// infinite loop.
		for ( attribute in track ) {
			// Decode HTML entities.
			value = $( '<div/>' ).html( track[ attribute ] ).text();
			$settings.filter( '[data-setting="' + attribute + '"]' ).val( value );
		}
	},

	updateTitle: function() {
		var title = this.model.get( 'title' );
		this.$el.find( '.cue-track-title .text' ).text( title ? title : 'Title' );
	},

	/**
	 * Destroy the view's model.
	 *
	 * Avoid syncing to the server by triggering an event instead of
	 * calling destroy() directly on the model.
	 */
	destroy: function() {
		this.model.trigger( 'destroy', this.model );
	},

	remove: function() {
		this.$el.remove();
	}
});

module.exports = Track;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./track/artwork":10,"./track/audio":11}],10:[function(require,module,exports){
(function (global){
"use strict";
var TrackArtwork,
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	workflows = require( '../../modules/workflows' ),
	wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null);

TrackArtwork = wp.Backbone.View.extend({
	tagName: 'span',
	className: 'cue-track-artwork',
	template: wp.template( 'cue-playlist-track-artwork' ),

	events: {
		'click': 'select'
	},

	initialize: function( options ) {
		this.parent = options.parent;
		this.listenTo( this.model, 'change:artworkUrl', this.render );
	},

	render: function() {
		this.$el.html( this.template( this.model.toJSON() ) );
		this.parent.$el.toggleClass( 'has-artwork', ! _.isEmpty( this.model.get( 'artworkUrl' ) ) );
		return this;
	},

	select: function() {
		workflows.setModel( this.model ).get( 'selectArtwork' ).open();
	}
});

module.exports = TrackArtwork;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"../../modules/workflows":3}],11:[function(require,module,exports){
(function (global){
"use strict";
var TrackAudio,
	$ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	mejs = (typeof window !== "undefined" ? window['mejs'] : typeof global !== "undefined" ? global['mejs'] : null),
	settings = require( 'cue' ).settings(),
	workflows = require( '../../modules/workflows' ),
	wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null);

TrackAudio = wp.Backbone.View.extend({
	tagName: 'span',
	className: 'cue-track-audio',
	template: wp.template( 'cue-playlist-track-audio' ),

	events: {
		'click .cue-track-audio-selector': 'select'
	},

	initialize: function( options ) {
		this.parent = options.parent;

		this.listenTo( this.model, 'change:audioUrl', this.refresh );
		this.listenTo( this.model, 'destroy', this.cleanup );
	},

	render: function() {
		var $mediaEl, playerSettings,
			track = this.model.toJSON(),
			playerId = this.$el.find( '.mejs-audio' ).attr( 'id' );

		// Remove the MediaElement player object if the
		// audio file URL is empty.
		if ( '' === track.audioUrl && playerId ) {
			mejs.players[ playerId ].remove();
		}

		// Render the media element.
		this.$el.html( this.template( this.model.toJSON() ) );

		// Set up MediaElement.js.
		$mediaEl = this.$el.find( '.cue-audio' );

		if ( $mediaEl.length ) {
			// MediaElement traverses the DOM and throws an error if it
			// can't find a parent node before reaching <body>. It makes
			// sure the flash fallback won't exist within a <p> tag.

			// The view isn't attached to the DOM at this point, so an
			// error is thrown when reaching the top of the tree.

			// This hack makes it stop searching. The fake <body> tag is
			// removed in the success callback.
			// @see mediaelement-and-player.js:~1222
			$mediaEl.wrap( '<body></body>' );

			playerSettings = {
				//enablePluginDebug: true,
				features: [ 'playpause', 'current', 'progress', 'duration' ],
				pluginPath: settings.pluginPath,
				success: _.bind( function( mediaElement, domObject, t ) {
					var $fakeBody = $( t.container ).parent();

					// Allow current time bar to be skinned
					// based on the admin color scheme.
					t.current.removeClass( 'mejs-time-current' ).addClass( 'cuemejs-time-current wp-ui-highlight' );

					// Remove the fake <body> tag.
					if ( $.nodeName( $fakeBody.get( 0 ), 'body' ) ) {
						$fakeBody.replaceWith( $fakeBody.get( 0 ).childNodes );
					}
				}, this ),
				error: function( el ) {
					var $el = $( el ),
						$parent = $el.closest( '.cue-track' ),
						playerId = $el.closest( '.mejs-audio' ).attr( 'id' );

					// Remove the audio element if there is an error.
					mejs.players[ playerId ].remove();
					$parent.find( 'audio' ).remove();
				}
			};

			// Hack to allow .m4a files.
			// @link https://github.com/johndyer/mediaelement/issues/291
			if ( 'm4a' === $mediaEl.attr( 'src' ).split( '.' ).pop() ) {
				playerSettings.pluginVars = 'isvideo=true';
			}

			$mediaEl.mediaelementplayer( playerSettings );
		}

		return this;
	},

	refresh: function( e ) {
		var track = this.model.toJSON(),
			playerId = this.$el.find( '.mejs-audio' ).attr( 'id' ),
			player = playerId ? mejs.players[ playerId ] : null;

		if ( player && '' !== track.audioUrl ) {
			player.pause();
			player.setSrc( track.audioUrl );
		} else {
			this.render();
		}
	},

	cleanup: function() {
		var playerId = this.$el.find( '.mejs-audio' ).attr( 'id' ),
			player = playerId ? mejs.players[ playerId ] : null;

		if ( player ) {
			player.remove();
		}
	},

	select: function() {
		workflows.setModel( this.model ).get( 'selectAudio' ).open();
	}
});

module.exports = TrackAudio;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"../../modules/workflows":3,"cue":"cue"}],"cue":[function(require,module,exports){
(function (global){
"use strict";
var _ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null);

function Application() {
	var settings = {};

	_.extend( this, {
		controller: {},
		l10n: {},
		model: {},
		view: {}
	});

	this.settings = function( options ) {
		if ( options ) {
			_.extend( settings, options );
		}

		if ( settings.l10n ) {
			this.l10n = _.extend( this.l10n, settings.l10n );
			delete settings.l10n;
		}

		return settings || {};
	};
}

global.cue = global.cue || new Application();
module.exports = global.cue;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}]},{},[4]);
