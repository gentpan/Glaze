<?php
defined( 'ABSPATH' ) || exit;

function westnote_glaze_comment_author_key( $comment ) {
	if ( $comment->user_id ) {
		return 'user:' . (int) $comment->user_id;
	}
	return $comment->comment_author_email ? 'guest:' . strtolower( $comment->comment_author_email ) : '';
}

function westnote_glaze_post_card_avatar_comments( $post_id ) {
	$avatars     = array();
	$seen        = array();
	$query       = array(
		'post_id' => $post_id,
		'status'  => 'approve',
		'type'    => 'comment',
		'number'  => 20,
		'orderby' => array( 'comment_date_gmt' => 'DESC', 'comment_ID' => 'DESC' ),
		'offset'  => 0,
	);

	do {
		$comments = get_comments( $query );
		foreach ( $comments as $comment ) {
			$key = westnote_glaze_comment_author_key( $comment ) ?: 'name:' . trim( $comment->comment_author );
			if ( isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;

			$user = $comment->user_id ? get_userdata( (int) $comment->user_id ) : false;
			if ( ! $comment->user_id && $comment->comment_author_email ) {
				$user = get_user_by( 'email', $comment->comment_author_email );
				if ( $user ) {
					$account_key = 'user:' . $user->ID;
					if ( isset( $seen[ $account_key ] ) ) {
						continue;
					}
					$seen[ $account_key ] = true;
				}
			}
			if ( $user && user_can( $user, 'manage_options' ) ) {
				continue;
			}

			$avatars[] = $comment;
			if ( 5 === count( $avatars ) ) {
				return $avatars;
			}
		}
		$query['offset'] += $query['number'];
	} while ( count( $comments ) === $query['number'] );

	return $avatars;
}

function westnote_glaze_comment_site_key( $url ) {
	$parts = wp_parse_url( esc_url_raw( $url, array( 'http', 'https' ) ) );
	if ( empty( $parts['host'] ) ) {
		return '';
	}

	$host = preg_replace( '/^www\./', '', strtolower( rtrim( $parts['host'], '.' ) ) );
	$port = isset( $parts['port'] ) && ! in_array( $parts['port'], array( 80, 443 ), true ) ? ':' . $parts['port'] : '';
	return $host . $port . rtrim( $parts['path'] ?? '', '/' );
}

function westnote_glaze_comment_browser( $agent ) {
	$browsers = array(
		'MicroMessenger'       => array( 'WeChat', '' ),
		'QQBrowser'            => array( 'QQ Browser', '' ),
		'EdgiOS|EdgA|Edg|Edge' => array( 'Edge', 'microsoft-edge' ),
		'OPR|OPiOS|Opera'      => array( 'Opera', 'opera' ),
		'SamsungBrowser'       => array( 'Samsung Internet', 'samsung-browser' ),
		'HuaweiBrowser'        => array( 'Huawei Browser', '' ),
		'Vivaldi'              => array( 'Vivaldi', 'vivaldi' ),
		'UCBrowser'            => array( 'UC Browser', '' ),
		'YaBrowser'            => array( 'Yandex Browser', '' ),
		'Brave'                => array( 'Brave', 'brave' ),
		'DuckDuckGo'           => array( 'DuckDuckGo', 'duckduckgo' ),
		'Waterfox'             => array( 'Waterfox', 'waterfox' ),
		'LibreWolf'            => array( 'LibreWolf', 'librewolf' ),
		'Floorp'               => array( 'Floorp', 'floorp' ),
		'FxiOS|Firefox'        => array( 'Firefox', 'firefox' ),
		'Chromium'             => array( 'Chromium', 'chromium' ),
		'CriOS|Chrome'         => array( 'Chrome', 'google-chrome' ),
	);
	foreach ( $browsers as $token => list( $name, $icon ) ) {
		if ( preg_match( '~\b(?:' . $token . ')/([0-9]{1,4})\b~i', $agent, $match ) ) {
			return array( 'label' => $name . ' ' . $match[1], 'icon' => $icon );
		}
	}
	if ( preg_match( '~\bVersion/([0-9]{1,4})\b.*\bSafari/~i', $agent, $match ) ) {
		return array( 'label' => 'Safari ' . $match[1], 'icon' => 'safari' );
	}
	if ( preg_match( '~\b(?:MSIE |Trident/.*\brv:)([0-9]{1,4})\b~i', $agent, $match ) ) {
		return array( 'label' => 'Internet Explorer ' . $match[1], 'icon' => 'internetexplorer' );
	}
	return array( 'label' => __( 'Unknown browser', 'glaze' ), 'icon' => 'globe' );
}

function westnote_glaze_comment_system( $agent ) {
	$systems = array(
		'Windows Phone'        => array( 'Windows Phone', 'windows' ),
		'iPad'                 => array( 'iPadOS', 'ios' ),
		'iPhone|iPod'          => array( 'iOS', 'ios' ),
		'HarmonyOS|OpenHarmony' => array( 'HarmonyOS', 'harmonyos' ),
		'Android'              => array( 'Android', 'android' ),
		'Windows'              => array( 'Windows', 'windows' ),
		'Macintosh|Mac OS X'   => array( 'macOS', 'apple' ),
		'CrOS'                 => array( 'ChromeOS', '' ),
		'Ubuntu'               => array( 'Ubuntu', 'ubuntu' ),
		'Debian'               => array( 'Debian', 'debian' ),
		'Fedora'               => array( 'Fedora', 'fedora' ),
		'FreeBSD'              => array( 'FreeBSD', 'freebsd' ),
		'OpenBSD'              => array( 'OpenBSD', 'openbsd' ),
		'NetBSD'               => array( 'NetBSD', 'netbsd' ),
		'Linux'                => array( 'Linux', 'linux' ),
	);
	foreach ( $systems as $token => list( $name, $icon ) ) {
		if ( preg_match( '~\b(?:' . $token . ')\b~i', $agent ) ) {
			return array( 'label' => $name, 'icon' => $icon );
		}
	}
	return null;
}

function westnote_glaze_render_comment_body( $comment, $options ) {
	$text   = apply_filters( 'comment_text', get_comment_text( $comment ), $comment, array() );
	$parent = $comment->comment_parent ? get_comment( $comment->comment_parent ) : null;
	if ( ! $parent || '1' !== (string) $parent->comment_approved || (int) $parent->comment_post_ID !== (int) $comment->comment_post_ID ) {
		return $text;
	}

	$name = get_comment_author( $parent );
	$plain = ltrim( html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	if ( preg_match( '/\A[@＠]' . preg_quote( $name, '/' ) . '(?=[\s:：,，]|$)/u', $plain ) ) {
		return $text;
	}
	$mention = '<a class="comment-reply-mention" href="' . esc_url( get_comment_link( $parent, $options ) ) . '">@' . esc_html( $name ) . '</a> ';
	if ( preg_match( '/\A\s*<p(?=[\s>])(?:[^>"\']|"[^"]*"|\'[^\']*\')*>\s*/i', $text, $opening ) ) {
		return substr_replace( $text, $mention, strlen( $opening[0] ), 0 );
	}
	return $mention . $text;
}

final class WestNote_Glaze_Comment_Walker extends Walker_Comment {
	private $friend_sites;
	private $positions;

	public function __construct( $post_id ) {
		$this->friend_sites = array();
		foreach ( get_bookmarks( array( 'hide_invisible' => true ) ) as $bookmark ) {
			$site = westnote_glaze_comment_site_key( $bookmark->link_url );
			if ( '' !== $site ) {
				$this->friend_sites[] = $site;
			}
		}
		$this->positions = array_flip(
			get_comments(
				array(
					'post_id'                   => $post_id,
					'parent'                    => 0,
					'status'                    => 'approve',
					'type'                      => 'comment',
					'fields'                    => 'ids',
					'orderby'                   => array( 'comment_date_gmt' => 'ASC', 'comment_ID' => 'ASC' ),
					'update_comment_meta_cache' => false,
					'update_comment_post_cache' => false,
				)
			)
		);
	}

	public function get_floor_count() {
		return count( $this->positions );
	}

	private function is_blogroll_comment( $comment ) {
		$site = westnote_glaze_comment_site_key( $comment->comment_author_url );
		foreach ( $this->friend_sites as $friend ) {
			if ( $site === $friend || str_starts_with( $site, $friend . '/' ) ) {
				return true;
			}
		}
		return false;
	}

	protected function html5_comment( $comment, $depth, $args ) {
		$id       = (int) $comment->comment_ID;
		$position = $this->positions[ $id ] ?? null;
		$system   = westnote_glaze_comment_system( $comment->comment_agent );
		if ( 'macOS' === ( $system['label'] ?? '' ) ) {
			$version = westnote_glaze_macos_version( get_comment_meta( $id, '_glaze_macos_version', true ) );
			if ( '' !== $version ) {
				$system['label'] .= ' ' . $version;
			}
		}
		get_template_part(
			'template-parts/comment-row',
			null,
			array(
				'comment'      => $comment,
				'depth'        => $depth,
				'options'      => $args,
				'has_children' => $this->has_children,
				'floor'        => null === $position ? 0 : $position + 1,
				'is_blogroll'  => $this->is_blogroll_comment( $comment ),
				'is_admin'     => $comment->user_id && user_can( (int) $comment->user_id, 'manage_options' ),
				'browser'      => westnote_glaze_comment_browser( $comment->comment_agent ),
				'system'       => $system,
			)
		);
	}

	protected function ping( $comment, $depth, $args ) {
		?>
		<li id="comment-<?php comment_ID(); ?>" <?php comment_class( '', $comment ); ?>>
			<div class="comment-body">
				<?php esc_html_e( 'Pingback:' ); ?> <?php comment_author_link( $comment ); ?>
			</div>
		<?php
	}
}
