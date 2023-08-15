(function($) {
	var toMinutes = function(seconds) {
		seconds = Math.floor(seconds);
		min_part = Math.floor(seconds/60);
		sec_part = seconds % 60;
		if (sec_part < 10) {
			sec_part = '0' + sec_part;
		}
		return min_part + ":" + sec_part;
	}

	var handleSlidePlayer = function($scope, player_selector) {
        $scope.find(player_selector).each(function() {

			var item_hover_color = $(this).data("entryhbgcolor");
			$(this).find('.swp_music_player_entry.wpb_smc_elt').each(function(){
				$(this).hover(
					function() {
						$(this).css("background-color", item_hover_color);
					}, function() {
						$(this).css("background-color", "transparent");
					}
				);
			});

			var $player = $(this);
			var player_id = $player.attr("id");
			var $play_btn = $player.find('.fa-play.player_play');
			var $pause_btn = $player.find('.fa-pause');
			var $fwd_btn = $player.find('.fa-step-forward');
			var $bkw_btn = $player.find('.fa-step-backward');		
			var $first_song = $player.find('.swp_music_player_entry').first();
			var $last_song = $player.find('.swp_music_player_entry').last();
			var $playing_song_name = $player.find('.current_song_name');
			var $playing_album_name = $player.find('.current_album_name');
			var $time_slider = $player.find('.player_time_slider');
			var $song_duration = $player.find('.song_duration');
			var $song_current_progress = $player.find('.song_current_progress');
			var autoplay = $player.data('autoplay');
			var playmode = $player.data('playmode');
			var stop_on_playlist_end = $player.data('stopplaylistend');
			var repeatmode = $player.data("repeatmode");
			var shuffle_btn_on = false, repeat_btn_on = false;
			var $ps_elt = $player.find('.compact-playback-speed');
			var $ps_val = $ps_elt.find('.ps-val');
			var $ps_opts = $player.find('ul.compact-ps-opts');
			var other_mpfe = new Array();


			$('.swp_music_player').not('#' + player_id).each(function(){
				other_mpfe.push($(this).attr('id'));
			});

			$ps_val.click(function(){
				$ps_opts.toggle();
			})
			$('.compact-ps-opt').click(function(){
				var new_pstext = $(this).text();
				var new_psval = new_pstext.substring(0,new_pstext.length - 1)
				$ps_opts.toggle();
				$ps_val.text(new_pstext);
				$player.find('audio').each(function() {
					$(this).get(0).playbackRate = new_psval;
				});
			})

			function handleCoverImg($crt_elt) {
				if(!$player.data('playerimg')) {
					return;
				}

				if (!$crt_elt.data('trackimg')) {
					/*set the album default img*/
					$player.find('.music_player_left').removeAttr("style");
					return;
				}

				/*song individual cover*/
				$player.find('.music_player_left').css('background-image', 'url(' + $crt_elt.data('trackimg') + ')');
			}

			function handleAlbumInfoForCompact($crt_elt) {
				if (!$player.hasClass('compact-player')) {
					return;
				}

				$playing_album_name.text($crt_elt.find('.player_song_name').data('albumname'));
			}

			function stopOtherPlayers() {
				other_mpfe.forEach(function(mpfe_player_id){
					var $crt_play = $('#' + mpfe_player_id).find('.swp_music_player_entry.now_playing');
					if ($crt_play.length){
						$crt_play.find('audio').get(0).pause();
						$('#' + mpfe_player_id).find('.fa-play.player_play').removeClass("display_none");
						$('#' + mpfe_player_id).find('.fa-pause').addClass("display_none");			
					}
				});
			}

			$player.find('.swp_music_player_entry').each(function(){
				var $player_entry = $(this);
				var audio = new Audio($player_entry.data("mediafile"));
				audio.type= 'audio/mpeg';
				audio.preload = 'metadata';
				$(this).append(audio);



				audio.onloadedmetadata = function() {
					$player_entry.find('.entry_duration').text(toMinutes(audio.duration));
					if ($first_song.is($player_entry)) {
						$song_duration.text(toMinutes(audio.duration));
					}
				};

				audio.onended  = function() {
					var $crt_elt = $player.find('.swp_music_player_entry.now_playing');
					var $next_elt = get_next_player_elt($crt_elt);

					$playing_song_name.text($next_elt.find('.player_song_name').text());
					$song_duration.text(toMinutes($next_elt.find('audio').get(0).duration));
					$crt_elt.removeClass('now_playing');
					$next_elt.addClass('now_playing');

					if (("yes" == stop_on_playlist_end) && (!$crt_elt.next().length)) {
						$play_btn.removeClass("display_none");
						$pause_btn.addClass("display_none");
						
						return;
					}

					$next_elt.find('audio').get(0).play();
					stopOtherPlayers();
					handleCoverImg($next_elt);
					handleAlbumInfoForCompact($next_elt);
					$play_btn.addClass("display_none");
					$pause_btn.removeClass("display_none");
				};			

				audio.addEventListener("timeupdate", function() {
				    var currentTime = audio.currentTime;
				    var duration = audio.duration;
				    $time_slider.stop(true,true).css('width', (currentTime +.25)/duration*100+'%');
				    $song_current_progress.text(toMinutes(currentTime));
				});			
			});

			/*load the 1st song*/
			$first_song.addClass("now_playing");
			$playing_song_name.text($first_song.find('.player_song_name').text());
			handleAlbumInfoForCompact($first_song);

			$song_current_progress.text("0:00");
			if ("yes" == autoplay) {
				var fp_response = $first_song.find('audio').get(0).play();
				handleCoverImg($first_song);
				if (fp_response!== undefined) {
					fp_response.then(_ => {
						$play_btn.toggleClass("display_none");
						$pause_btn.toggleClass("display_none");
						stopOtherPlayers();
					}).catch(error => {
						$(document).click(function(e) {
							if (!$first_song.hasClass("autoplay_loaded")) {
								$first_song.find('audio').get(0).play();
								$play_btn.toggleClass("display_none");
								$pause_btn.toggleClass("display_none");
								$first_song.addClass("autoplay_loaded");
							}
						});
					});
				}
			}

			$play_btn.unbind().click(function() {
				stopOtherPlayers();
				var $crt_elt = $player.find('.swp_music_player_entry.now_playing');
				$crt_elt.find('audio').get(0).play();
				stopOtherPlayers();
				handleCoverImg($crt_elt);
				handleAlbumInfoForCompact($crt_elt);
				$play_btn.addClass("display_none");
				$pause_btn.removeClass("display_none");
			});
			$pause_btn.unbind().click(function() {
				var $crt_elt = $player.find('.swp_music_player_entry.now_playing');
				$crt_elt.find('audio').get(0).pause();
				$play_btn.removeClass("display_none");
				$pause_btn.addClass("display_none");
			});

			$fwd_btn.unbind().click(function() {
				var $crt_elt = $player.find('.swp_music_player_entry.now_playing');
				$crt_elt.find('audio').get(0).pause();

				var $next_elt = get_next_player_elt($crt_elt);

				$playing_song_name.text($next_elt.find('.player_song_name').text());
				$next_elt.find('audio').get(0).play();
				stopOtherPlayers();
				handleCoverImg($next_elt);
				handleAlbumInfoForCompact($next_elt);
				$song_duration.text(toMinutes($next_elt.find('audio').get(0).duration));
				$crt_elt.removeClass('now_playing');
				$next_elt.addClass('now_playing');
				$play_btn.addClass("display_none");
				$pause_btn.removeClass("display_none");
			});

			$bkw_btn.unbind().click(function() {
				var $crt_elt = $player.find('.swp_music_player_entry.now_playing');
				$crt_elt.find('audio').get(0).pause();
				var $prev_elt = $crt_elt.prev();
				if (!$prev_elt.length) {
					$prev_elt = $last_song;
				}
				$playing_song_name.text($prev_elt.find('.player_song_name').text());
				$prev_elt.find('audio').get(0).play();
				stopOtherPlayers();
				handleCoverImg($prev_elt);
				handleAlbumInfoForCompact($prev_elt);
				$song_duration.text(toMinutes($prev_elt.find('audio').get(0).duration));
				$crt_elt.removeClass('now_playing');
				$prev_elt.addClass('now_playing');
				$play_btn.addClass("display_none");
				$pause_btn.removeClass("display_none");
			});

			$player.find('.player_entry_left').click(function(){
				var $next_elt = $(this).parent();
				var $crt_elt = $player.find('.swp_music_player_entry.now_playing');
				$crt_elt.find('audio').get(0).pause();
				$crt_elt.removeClass('now_playing');

				$next_elt.addClass('now_playing');
				$next_elt.find('audio').get(0).play();
				stopOtherPlayers();
				handleCoverImg($next_elt);
				handleAlbumInfoForCompact($next_elt);
				$song_duration.text(toMinutes($(this).parent().find('audio').get(0).duration));
				$playing_song_name.text($(this).find('.player_song_name').text());

				$play_btn.addClass("display_none");
				$pause_btn.removeClass("display_none");			
			});

			$player.find('.player_time_slider_base').click(function(e){
				var $slider_elt = $(this);
				var click_pos = e.pageX - Math.floor($slider_elt.offset().left);
				var elt_width = $slider_elt.width();
				var percent_progress = Math.floor(click_pos/elt_width*100);
				$time_slider.width(percent_progress + "%");

				var $crt_elt = $player.find('.swp_music_player_entry.now_playing');
				$crt_elt.find('audio').get(0).currentTime = $crt_elt.find('audio').get(0).duration * (percent_progress/100);
			});

			var get_next_player_elt = function($crt_elt) {
				if ("repeat" == playmode) {
					if (("current_song" == repeatmode) && repeat_btn_on) {
						return $crt_elt;
					}

					var $next_elt = $crt_elt.next();
					if (!$next_elt.length) {
						$next_elt = $first_song;
					}
					return $next_elt;
				}
				/*shuffle*/
				var $playlist = $player.find('.swp_music_player_entry').not('.now_playing').toArray();
				return jQuery($playlist[Math.floor(Math.random() * $playlist.length)]);
			}

			$player.find('i.playback-shuffle').click(function(e){
				if(shuffle_btn_on) {
					$player.attr('data-playmode', "repeat");
					playmode = "repeat";
					shuffle_btn_on = false;
				} else {
					$player.attr('data-playmode', "shuffle");
					playmode = "shuffle";
					shuffle_btn_on = true;
				}
				$(this).toggleClass("is_active");
			});
			$player.find('i.playback-repeat').click(function(e){
				if (repeat_btn_on) {
					if (shuffle_btn_on) {
						$player.attr('data-playmode', "shuffle");
						playmode = "shuffle";
					}
					repeat_btn_on = false;
				} else {
					$player.attr('data-playmode', "repeat");
					playmode = "repeat";
					repeat_btn_on = true;
				}
				$(this).toggleClass("is_active");
			});

        });
	}

    var MPFESlideMusicPlayer = function($scope, $) {
		$scope.find('.mpfe-sr-helper').click(function(e){
			e.preventDefault();
		});

		handleSlidePlayer($scope, '.swp_music_player');

    }

	var MPFESlideCompactPlayer = function($scope, $) {
		handleSlidePlayer($scope, '.swp_music_player.compact-player');

		$('.mpfe-compact-list').click(function(){
			$(this).closest('.swp_music_player').find('.swp-compact-playlist').addClass('list-visible');
			$(window).addClass('swp-overflow-y-hidden');
		});
		$('.compact-close-playlist-container').click(function(){
			$(this).closest('.swp-compact-playlist').removeClass('list-visible');
			$(window).removeClass('swp-overflow-y-hidden');
		});
	}
	

	$(window).on("elementor/frontend/init", function() {
        elementorFrontend.hooks.addAction(
            "frontend/element_ready/slide-music-player-free.default",
            MPFESlideMusicPlayer
        );
        elementorFrontend.hooks.addAction(
            "frontend/element_ready/slide-compact-player.default",
            MPFESlideCompactPlayer
        );
	});

})(jQuery);