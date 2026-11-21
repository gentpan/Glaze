<?php
$emojis = array(
	'😀' => _x( 'Happy', 'comment emoji', 'glaze' ),
	'😂' => _x( 'Tears of joy', 'comment emoji', 'glaze' ),
	'😊' => _x( 'Smile', 'comment emoji', 'glaze' ),
	'😍' => _x( 'Heart eyes', 'comment emoji', 'glaze' ),
	'🥰' => _x( 'Smiling with hearts', 'comment emoji', 'glaze' ),
	'😎' => _x( 'Cool', 'comment emoji', 'glaze' ),
	'🤔' => _x( 'Thinking', 'comment emoji', 'glaze' ),
	'🤭' => _x( 'Giggle', 'comment emoji', 'glaze' ),
	'😅' => _x( 'Nervous laugh', 'comment emoji', 'glaze' ),
	'😮' => _x( 'Surprised', 'comment emoji', 'glaze' ),
	'🙃' => _x( 'Upside down', 'comment emoji', 'glaze' ),
	'😉' => _x( 'Wink', 'comment emoji', 'glaze' ),
	'🥹' => _x( 'Holding back tears', 'comment emoji', 'glaze' ),
	'😭' => _x( 'Crying', 'comment emoji', 'glaze' ),
	'😴' => _x( 'Sleepy', 'comment emoji', 'glaze' ),
	'🤩' => _x( 'Star-struck', 'comment emoji', 'glaze' ),
	'👍' => _x( 'Thumbs up', 'comment emoji', 'glaze' ),
	'👎' => _x( 'Thumbs down', 'comment emoji', 'glaze' ),
	'👌' => _x( 'OK', 'comment emoji', 'glaze' ),
	'💪' => _x( 'Flexed biceps', 'comment emoji', 'glaze' ),
	'👏' => _x( 'Applause', 'comment emoji', 'glaze' ),
	'🙏' => _x( 'Thanks', 'comment emoji', 'glaze' ),
	'❤️' => _x( 'Heart', 'comment emoji', 'glaze' ),
	'🎉' => _x( 'Celebration', 'comment emoji', 'glaze' ),
);
?>
<div class="glaze-comment-emojis wp-exclude-emoji" data-glaze-comment-emojis role="group" aria-label="<?php esc_attr_e( 'Insert an emoji', 'glaze' ); ?>" hidden>
	<?php foreach ( $emojis as $emoji => $label ) : ?>
		<button type="button" data-glaze-comment-emoji="<?php echo esc_attr( $emoji ); ?>" aria-label="<?php echo esc_attr( $label ); ?>" title="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $emoji ); ?></button>
	<?php endforeach; ?>
</div>
