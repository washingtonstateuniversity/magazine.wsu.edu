<?php

WP_CLI::add_command( 'wsu-magazine', 'WSU_Magazine_Command' );

class WSU_Magazine_Command extends WP_CLI_Command {

	/**
	 * @subcommand create-guest-authors
	 */
	public function create_guest_authors() {
		global $coauthors_plus;

		$args = array(
			'post_type' => 'wsu_magazine_author',
			'posts_per_page' => 5000,
		);

		$query = new WP_Query( $args );

		while ( $query->have_posts() ) {
			$query->the_post();

			$old_author_name = get_the_title();
			$old_author_slug = explode( '’', $old_author_name );
			$old_author_slug = esc_attr( trim( $old_author_slug[0] ) );
			$old_author_id = get_post_meta( get_the_ID(), '_magazine_author_id', true );

			$args = array(
				'display_name' => $old_author_name,
				'user_login' => sanitize_title( $old_author_slug ),
			);

			$new_post_id = $coauthors_plus->guest_authors->create( $args );

			if ( is_wp_error( $new_post_id ) ) {
				WP_CLI::line( $old_author_name . ' | ' . esc_attr( $old_author_id ) . ' | ' . $new_post_id->get_error_message() );
			} else {
				update_post_meta( $new_post_id, '_magazine_author_id', absint( $old_author_id ) );
				WP_CLI::line( $old_author_name . ' ' . esc_attr( $old_author_id ) . ' ' . $new_post_id );
			}
		}
	}

	/**
	 * @subcommand attach-guest-authors
	 */
	public function attach_guest_authors() {
		global $wpdb, $coauthors_plus;

		$authors = array();

		$all_data = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_magazine_author_id'" );

		foreach ( $all_data as $data ) {
			$author = get_post( $data->post_id );

			if ( 'guest-author' !== $author->post_type ) {
				continue;
			}

			$author_article_id = absint( $data->meta_value );

			$authors[ $author_article_id ] = $data->post_id;
		}

		foreach ( $all_data as $data ) {
			$post = get_post( $data->post_id );

			if ( 'post' !== $post->post_type ) {
				continue;
			}

			$author_article_id = absint( $data->meta_value );

			if ( isset( $authors[ $author_article_id ] ) ) {
				$coauthor = $coauthors_plus->get_coauthor_by( 'id', $authors[ $author_article_id ] );
				$coauthors_plus->add_coauthors( $data->post_id, array( $coauthor->user_nicename ), $append = true );
				WP_CLI::line( 'Assign ' . $authors[ $author_article_id ] . ' to ' . $data->post_id );
			}
		}
	}

	/**
	 * @subcommand assign-sections
	 */
	public function assign_sections() {
		global $wpdb;

		$section_data = array(
			'6' => 'Features',
			'5' => 'Features',
			'4' => 'Features',
			'7' => 'Features',
			'8' => 'Features',
			'9' => 'Features',
			'10' => 'Fiction',
			'11' => 'Features',
			'12' => 'Panoramas',
			'13' => 'Panoramas',
			'14' => 'Panoramas',
			'15' => 'Perspective',
			'16' => 'Panoramas',
			'17' => 'Panoramas',
			'18' => 'Panoramas',
			'19' => 'Sports',
			'20' => 'Sports',
			'21' => 'Sports',
			'22' => 'Panoramas',
			'23' => 'Panoramas',
			'24' => 'Panoramas',
			'25' => 'Panoramas',
			'26' => 'Tracking',
			'27' => 'Tracking',
			'28' => 'Tracking',
			'29' => 'Tracking',
			'30' => 'Tracking',
			'31' => 'Tracking',
			'32' => 'Tracking',
			'33' => 'Tracking',
			'34' => 'Tracking',
			'35' => 'Features',
			'36' => 'Features',
			'37' => 'Features',
			'38' => 'Features',
			'39' => 'Field Notes',
			'40' => 'Field Notes',
			'41' => 'Panoramas',
			'42' => 'Panoramas',
			'43' => 'Panoramas',
			'44' => 'Panoramas',
			'45' => 'Perspective',
			'46' => 'Panoramas',
			'47' => 'Panoramas',
			'48' => 'Sports',
			'49' => 'Panoramas',
			'50' => 'Research',
			'51' => 'Panoramas',
			'52' => 'Panoramas',
			'53' => 'Panoramas',
			'54' => 'Tracking',
			'55' => 'Tracking',
			'56' => 'Tracking',
			'57' => 'Tracking',
			'58' => 'Tracking',
			'59' => 'Tracking',
			'60' => 'Tracking',
			'61' => 'Features',
			'62' => 'Features',
			'63' => 'Features',
			'64' => 'Features',
			'65' => 'Features',
			'66' => 'Field Notes',
			'67' => 'Panoramas',
			'68' => 'Panoramas',
			'69' => 'Panoramas',
			'70' => 'Panoramas',
			'71' => 'Perspective',
			'72' => 'Panoramas',
			'73' => 'Sports',
			'74' => 'Sports',
			'75' => 'Careers',
			'76' => 'Panoramas',
			'77' => 'Panoramas',
			'78' => 'Panoramas',
			'79' => 'Panoramas',
			'80' => 'Panoramas',
			'81' => 'Panoramas',
			'82' => 'Panoramas',
			'83' => 'Panoramas',
			'84' => 'Panoramas',
			'85' => 'Tracking',
			'86' => 'Tracking',
			'87' => 'Tracking',
			'88' => 'Tracking',
			'89' => 'Tracking',
			'90' => 'Tracking',
			'91' => 'Tracking',
			'92' => 'Tracking',
			'93' => 'Features',
			'94' => 'Features',
			'95' => 'Departments',
			'96' => 'Departments',
			'97' => 'Features',
			'98' => 'Features',
			'99' => 'Features',
			'100' => 'Sports',
			'101' => 'Features',
			'102' => 'Features',
			'103' => 'Features',
			'104' => 'Features',
			'105' => 'Features',
			'106' => 'Field Notes',
			'107' => 'Features',
			'108' => 'Features',
			'109' => 'Features',
			'110' => 'Features',
			'111' => 'Features',
			'112' => 'Features',
			'113' => 'Features',
			'114' => 'Features',
			'115' => 'Features',
			'116' => 'Features',
			'117' => 'Features',
			'118' => 'Features',
			'119' => 'Field Notes',
			'120' => 'Panoramas',
			'121' => 'Panoramas',
			'122' => 'Panoramas',
			'123' => 'Panoramas',
			'124' => 'Features',
			'125' => 'Features',
			'126' => 'Features',
			'130' => 'Features',
			'131' => 'Features',
			'132' => 'Features',
			'133' => 'Panoramas',
			'134' => 'Panoramas',
			'135' => 'Panoramas',
			'136' => 'Panoramas',
			'137' => 'Panoramas',
			'138' => 'Perspective',
			'139' => 'Food &amp; Forage',
			'140' => 'Sports',
			'141' => 'Panoramas',
			'142' => 'Features',
			'143' => 'Features',
			'144' => 'Essay',
			'145' => 'Panoramas',
			'146' => 'Panoramas',
			'147' => 'Panoramas',
			'148' => 'Panoramas',
			'149' => 'Panoramas',
			'150' => 'Panoramas',
			'151' => 'Panoramas',
			'152' => 'Panoramas',
			'153' => 'Sports',
			'154' => 'Departments',
			'155' => 'In Season',
			'156' => 'Short subject',
			'157' => 'Tracking',
			'158' => 'Tracking',
			'159' => 'Tracking',
			'160' => 'Features',
			'161' => 'Features',
			'162' => 'Features',
			'163' => 'Features',
			'164' => 'Essay',
			'165' => 'Sports',
			'166' => 'In Season',
			'167' => 'Panoramas',
			'168' => 'Panoramas',
			'169' => 'Panoramas',
			'170' => 'Panoramas',
			'171' => 'Panoramas',
			'172' => 'Panoramas',
			'173' => 'Panoramas',
			'174' => 'Tracking',
			'175' => 'Tracking',
			'176' => 'Last Words',
			'177' => 'First Words',
			'178' => 'Letters',
			'179' => 'Tracking',
			'180' => 'Panoramas',
			'181' => 'Panoramas',
			'182' => 'Panoramas',
			'183' => 'Tracking',
			'184' => 'Tracking',
			'185' => 'Tracking',
			'186' => 'Tracking',
			'187' => 'Tracking',
			'188' => 'Features',
			'189' => 'Features',
			'190' => 'Features',
			'191' => 'Features',
			'192' => 'Features',
			'193' => 'Features',
			'194' => 'Essay',
			'195' => 'Panoramas',
			'196' => 'Panoramas',
			'197' => 'Panoramas',
			'198' => 'Panoramas',
			'199' => 'Short subject',
			'200' => 'Sports',
			'201' => 'In Season',
			'202' => 'Tracking',
			'203' => 'Tracking',
			'204' => 'Tracking',
			'205' => 'Panoramas',
			'206' => 'Perspective',
			'207' => 'Panoramas',
			'208' => 'Panoramas',
			'209' => 'Panoramas',
			'210' => 'Sports',
			'211' => 'Features',
			'212' => 'Features',
			'213' => 'Features',
			'214' => 'Features',
			'215' => 'Panoramas',
			'216' => 'Panoramas',
			'217' => 'Panoramas',
			'218' => 'Panoramas',
			'219' => 'Panoramas',
			'220' => 'Panoramas',
			'221' => 'Panoramas',
			'222' => 'Panoramas',
			'223' => 'Panoramas',
			'224' => 'First Words',
			'225' => 'Sports',
			'226' => 'Last Words',
			'227' => 'Letters',
			'228' => 'In Season',
			'229' => 'Tracking',
			'230' => 'Tracking',
			'231' => 'Tracking',
			'232' => 'Features',
			'233' => 'Features',
			'234' => 'Features',
			'235' => 'Essay',
			'236' => 'Panoramas',
			'237' => 'Panoramas',
			'238' => 'Panoramas',
			'239' => 'Panoramas',
			'240' => 'Panoramas',
			'241' => 'First Words',
			'242' => 'Sports',
			'243' => 'Green Pages',
			'244' => 'Green Pages',
			'245' => 'In Season',
			'246' => 'Letters',
			'247' => 'Green Pages',
			'248' => 'Last Words',
			'249' => 'Tracking',
			'250' => 'Tracking',
			'251' => 'Tracking',
			'252' => 'Tracking',
			'253' => 'Features',
			'254' => 'Features',
			'255' => 'Features',
			'256' => 'Panoramas',
			'257' => 'Panoramas',
			'258' => 'Panoramas',
			'259' => 'Panoramas',
			'260' => 'Panoramas',
			'261' => 'Panoramas',
			'262' => 'Panoramas',
			'263' => 'First Words',
			'264' => 'Sports',
			'265' => 'Short subject',
			'266' => 'Letters',
			'267' => 'Last Words',
			'268' => 'In Season',
			'269' => 'Tracking',
			'270' => 'Tracking',
			'271' => 'Tracking',
			'272' => 'Features',
			'273' => 'Features',
			'274' => 'Features',
			'275' => 'Panoramas',
			'276' => 'Panoramas',
			'277' => 'Panoramas',
			'278' => 'Panoramas',
			'279' => 'Panoramas',
			'280' => 'Panoramas',
			'281' => 'Sports',
			'282' => 'Sports',
			'283' => 'In Season',
			'284' => 'Tracking',
			'285' => 'Tracking',
			'286' => 'Features',
			'287' => 'Features',
			'288' => 'Features',
			'289' => 'Features',
			'290' => 'Panoramas',
			'291' => 'Panoramas',
			'292' => 'Panoramas',
			'293' => 'Panoramas',
			'294' => 'Panoramas',
			'295' => 'Food &amp; Forage',
			'296' => 'Sports',
			'297' => 'Panoramas',
			'298' => 'Tracking',
			'299' => 'Tracking',
			'300' => 'Tracking',
			'301' => 'Features',
			'302' => 'Features',
			'303' => 'Features',
			'304' => 'Panoramas',
			'305' => 'Panoramas',
			'306' => 'Panoramas',
			'307' => 'Panoramas',
			'308' => 'Panoramas',
			'309' => 'Panoramas',
			'310' => 'Panoramas',
			'311' => 'First Words',
			'312' => 'Letters',
			'313' => 'Sports',
			'314' => 'Sports',
			'315' => 'In Season',
			'316' => 'Last Words',
			'317' => 'Tracking',
			'318' => 'Tracking',
			'319' => 'Tracking',
			'320' => 'Tracking',
			'321' => 'Features',
			'322' => 'Features',
			'323' => 'Features',
			'324' => 'Features',
			'325' => 'Field Notes',
			'326' => 'Sports',
			'327' => 'Panoramas',
			'328' => 'Panoramas',
			'329' => 'Panoramas',
			'330' => 'Panoramas',
			'331' => 'Panoramas',
			'332' => 'Panoramas',
			'333' => 'Panoramas',
			'334' => 'Panoramas',
			'335' => 'Panoramas',
			'336' => 'Panoramas',
			'337' => 'Sports',
			'338' => 'Tracking',
			'339' => 'Tracking',
			'340' => 'Tracking',
			'341' => 'Tracking',
			'342' => 'Tracking',
			'343' => 'Tracking',
			'344' => 'Tracking',
			'345' => 'Tracking',
			'346' => 'Features',
			'347' => 'Features',
			'348' => 'Features',
			'349' => 'Field Notes',
			'350' => 'Panoramas',
			'351' => 'Panoramas',
			'352' => 'Panoramas',
			'353' => 'Panoramas',
			'354' => 'Food &amp; Forage',
			'355' => 'Panoramas',
			'356' => 'Panoramas',
			'357' => 'Panoramas',
			'358' => 'Panoramas',
			'359' => 'Panoramas',
			'360' => 'Panoramas',
			'361' => 'Sports',
			'362' => 'Tracking',
			'363' => 'Tracking',
			'364' => 'Tracking',
			'365' => 'Tracking',
			'366' => 'Tracking',
			'367' => 'Tracking',
			'368' => 'Features',
			'369' => 'Features',
			'370' => 'Features',
			'371' => 'Panoramas',
			'372' => 'Panoramas',
			'373' => 'Panoramas',
			'374' => 'Perspective',
			'375' => 'Panoramas',
			'376' => 'Panoramas',
			'377' => 'Panoramas',
			'378' => 'Panoramas',
			'379' => 'Panoramas',
			'380' => 'A Sense of Place',
			'381' => 'Panoramas',
			'382' => 'Sports',
			'383' => 'Tracking',
			'384' => 'Tracking',
			'385' => 'Tracking',
			'386' => 'Tracking',
			'387' => 'Tracking',
			'388' => 'Panoramas',
			'389' => 'Tracking',
			'390' => 'Features',
			'391' => 'Features',
			'392' => 'Features',
			'393' => 'Features',
			'394' => 'Features',
			'395' => 'Panoramas',
			'396' => 'Panoramas',
			'397' => 'Panoramas',
			'398' => 'Panoramas',
			'399' => 'Panoramas',
			'400' => 'Food &amp; Forage',
			'401' => 'Panoramas',
			'402' => 'Panoramas',
			'403' => 'Food &amp; Forage',
			'404' => 'Panoramas',
			'405' => 'Panoramas',
			'406' => 'Panoramas',
			'407' => 'Panoramas',
			'408' => 'Panoramas',
			'409' => 'Sports',
			'410' => 'Tracking',
			'411' => 'Tracking',
			'412' => 'Tracking',
			'413' => 'Tracking',
			'414' => 'Tracking',
			'415' => 'Tracking',
			'416' => 'Features',
			'417' => 'Features',
			'418' => 'Features',
			'419' => 'Panoramas',
			'420' => 'Panoramas',
			'421' => 'Perspective',
			'422' => 'Panoramas',
			'423' => 'Panoramas',
			'424' => 'Panoramas',
			'425' => 'A Sense of Place',
			'426' => 'Panoramas',
			'427' => 'Panoramas',
			'428' => 'Panoramas',
			'429' => 'Panoramas',
			'430' => 'Panoramas',
			'431' => 'Sports',
			'432' => 'Sports',
			'433' => 'Tracking',
			'434' => 'Tracking',
			'435' => 'Tracking',
			'436' => 'Tracking',
			'437' => 'Tracking',
			'438' => 'Tracking',
			'439' => 'Tracking',
			'440' => 'Tracking',
			'441' => 'Tracking',
			'442' => 'Tracking',
			'443' => 'Features',
			'444' => 'Features',
			'445' => 'Features',
			'446' => 'Features',
			'447' => 'Panoramas',
			'448' => 'Panoramas',
			'449' => 'Panoramas',
			'450' => 'Panoramas',
			'451' => 'Panoramas',
			'452' => 'Panoramas',
			'453' => 'Departments',
			'454' => 'Panoramas',
			'455' => 'Panoramas',
			'456' => 'Panoramas',
			'457' => 'Sports',
			'458' => 'Perspective',
			'459' => 'Tracking',
			'460' => 'Tracking',
			'461' => 'Tracking',
			'462' => 'Tracking',
			'463' => 'Tracking',
			'464' => 'Tracking',
			'465' => 'Tracking',
			'466' => 'Tracking',
			'467' => 'Tracking',
			'468' => 'Tracking',
			'469' => 'Features',
			'470' => 'Features',
			'471' => 'Features',
			'472' => 'Features',
			'473' => 'Features',
			'474' => 'Panoramas',
			'475' => 'Panoramas',
			'476' => 'Panoramas',
			'477' => 'Panoramas',
			'478' => 'Panoramas',
			'479' => 'Panoramas',
			'480' => 'Panoramas',
			'481' => 'Panoramas',
			'482' => 'Panoramas',
			'483' => 'Panoramas',
			'484' => 'Panoramas',
			'485' => 'Panoramas',
			'486' => 'Panoramas',
			'487' => 'Sports',
			'488' => 'Tracking',
			'489' => 'Tracking',
			'490' => 'Tracking',
			'491' => 'Tracking',
			'492' => 'Tracking',
			'493' => 'Tracking',
			'494' => 'Tracking',
			'495' => 'Tracking',
			'496' => 'Features',
			'497' => 'Features',
			'498' => 'Features',
			'499' => 'Features',
			'500' => 'Features',
			'501' => 'Perspective',
			'502' => 'Panoramas',
			'503' => 'Panoramas',
			'504' => 'Panoramas',
			'505' => 'Panoramas',
			'506' => 'A Sense of Place',
			'507' => 'Panoramas',
			'508' => 'Panoramas',
			'509' => 'Panoramas',
			'510' => 'Panoramas',
			'511' => 'Panoramas',
			'512' => 'Panoramas',
			'513' => 'Panoramas',
			'514' => 'Panoramas',
			'515' => 'Panoramas',
			'516' => 'Panoramas',
			'517' => 'Panoramas',
			'518' => 'Panoramas',
			'519' => 'Panoramas',
			'520' => 'Panoramas',
			'521' => 'Panoramas',
			'522' => 'Panoramas',
			'523' => 'Perspective',
			'524' => 'Sports',
			'525' => 'Sports',
			'526' => 'Money',
			'527' => 'A Sense of Place',
			'528' => 'Tracking',
			'529' => 'Tracking',
			'530' => 'Tracking',
			'531' => 'Tracking',
			'532' => 'Tracking',
			'533' => 'Tracking',
			'534' => 'Tracking',
			'535' => 'Tracking',
			'536' => 'Features',
			'537' => 'Features',
			'538' => 'Features',
			'539' => 'Features',
			'540' => 'Panoramas',
			'541' => 'Panoramas',
			'542' => 'Panoramas',
			'543' => 'Panoramas',
			'544' => 'Panoramas',
			'545' => 'Panoramas',
			'546' => 'Panoramas',
			'547' => 'Panoramas',
			'548' => 'Panoramas',
			'549' => 'Panoramas',
			'550' => 'Panoramas',
			'551' => 'Panoramas',
			'552' => 'Panoramas',
			'553' => 'Panoramas',
			'554' => 'Careers',
			'555' => 'Sports',
			'556' => 'Sports',
			'557' => 'Sports',
			'558' => 'Tracking',
			'559' => 'Tracking',
			'560' => 'Tracking',
			'561' => 'Tracking',
			'562' => 'Tracking',
			'563' => 'Tracking',
			'564' => 'Tracking',
			'565' => 'Tracking',
			'566' => 'Ask Dr. Universe',
			'567' => 'Panoramas',
			'568' => 'Panoramas',
			'569' => 'A Sense of Place',
			'570' => 'Panoramas',
			'571' => 'Panoramas',
			'572' => 'Panoramas',
			'573' => 'Panoramas',
			'574' => 'Panoramas',
			'575' => 'Panoramas',
			'576' => 'Panoramas',
			'577' => 'Careers',
			'578' => 'Sports',
			'579' => 'Sports',
			'580' => 'Sports',
			'581' => 'Tracking',
			'582' => 'Tracking',
			'583' => 'Tracking',
			'584' => 'Tracking',
			'585' => 'Tracking',
			'586' => 'Tracking',
			'587' => 'Tracking',
			'588' => 'Tracking',
			'589' => 'Tracking',
			'590' => 'Features',
			'591' => 'Features',
			'592' => 'Features',
			'593' => 'Features',
			'594' => 'Features',
			'595' => 'Panoramas',
			'596' => 'Panoramas',
			'597' => 'Panoramas',
			'598' => 'Panoramas',
			'599' => 'Panoramas',
			'600' => 'Panoramas',
			'601' => 'Panoramas',
			'602' => 'Food &amp; Forage',
			'603' => 'Sports',
			'604' => 'Sports',
			'605' => 'Sports',
			'606' => 'Tracking',
			'607' => 'Tracking',
			'608' => 'Tracking',
			'609' => 'Tracking',
			'610' => 'Tracking',
			'611' => 'Tracking',
			'612' => 'Tracking',
			'613' => 'Tracking',
			'614' => 'Tracking',
			'615' => 'Tracking',
			'616' => 'Features',
			'617' => 'Features',
			'618' => 'Features',
			'619' => 'Features',
			'620' => 'Features',
			'621' => 'Features',
			'622' => 'Features',
			'623' => 'Features',
			'624' => 'Panoramas',
			'625' => 'Panoramas',
			'626' => 'Panoramas',
			'627' => 'Features',
			'628' => 'Features',
			'629' => 'Panoramas',
			'630' => 'Panoramas',
			'631' => 'Panoramas',
			'632' => 'Panoramas',
			'633' => 'Panoramas',
			'634' => 'Panoramas',
			'635' => 'Panoramas',
			'636' => 'Panoramas',
			'637' => 'Panoramas',
			'638' => 'Sports',
			'639' => 'Food &amp; Forage',
			'640' => 'Tracking',
			'641' => 'Tracking',
			'642' => 'Tracking',
			'643' => 'Panoramas',
			'644' => 'Panoramas',
			'645' => 'Food &amp; Forage',
			'646' => 'Tracking',
			'647' => 'Tracking',
			'648' => 'Tracking',
			'649' => 'Tracking',
			'650' => 'Tracking',
			'651' => 'Panoramas',
			'652' => 'Panoramas',
			'653' => 'Panoramas',
			'654' => 'Research',
			'655' => 'Sports',
			'656' => 'Sports',
			'657' => 'Money',
			'658' => 'Perspective',
			'659' => 'Panoramas',
			'660' => 'Panoramas',
			'661' => 'A Sense of Place',
			'662' => 'Tracking',
			'663' => 'Tracking',
			'664' => 'Tracking',
			'665' => 'Features',
			'666' => 'Features',
			'667' => 'Features',
			'668' => 'Features',
			'669' => 'Panoramas',
			'670' => 'Panoramas',
			'671' => 'Panoramas',
			'672' => 'Panoramas',
			'673' => 'Panoramas',
			'674' => 'Panoramas',
			'675' => 'Panoramas',
			'676' => 'Panoramas',
			'677' => 'Panoramas',
			'678' => 'Panoramas',
			'679' => 'Panoramas',
			'680' => 'Food &amp; Forage',
			'681' => 'Sports',
			'682' => 'Tracking',
			'683' => 'Tracking',
			'684' => 'Tracking',
			'685' => 'Tracking',
			'686' => 'Features',
			'687' => 'Features',
			'688' => 'Features',
			'689' => 'Panoramas',
			'690' => 'Panoramas',
			'691' => 'Panoramas',
			'692' => 'Panoramas',
			'693' => 'Panoramas',
			'694' => 'Panoramas',
			'695' => 'Food &amp; Forage',
			'696' => 'Sports',
			'697' => 'Tracking',
			'698' => 'Tracking',
			'699' => 'Tracking',
			'700' => 'Tracking',
			'701' => 'Tracking',
			'702' => 'Tracking',
			'703' => 'Tracking',
			'704' => 'Tracking',
			'705' => 'Tracking',
			'706' => 'Tracking',
			'707' => 'Panoramas',
			'708' => 'Panoramas',
			'709' => 'Perspective',
			'710' => 'Panoramas',
			'711' => 'Panoramas',
			'712' => 'Panoramas',
			'713' => 'Panoramas',
			'714' => 'Sports',
			'715' => 'Sports',
			'716' => 'Tracking',
			'717' => 'Tracking',
			'718' => 'Tracking',
			'719' => 'Tracking',
			'720' => 'Tracking',
			'721' => 'Tracking',
			'722' => 'Tracking',
			'723' => 'Tracking',
			'724' => 'Tracking',
			'725' => 'Features',
			'726' => 'Features',
			'727' => 'Panoramas',
			'728' => 'Panoramas',
			'729' => 'Panoramas',
			'730' => 'Panoramas',
			'731' => 'Panoramas',
			'732' => 'Panoramas',
			'733' => 'Panoramas',
			'734' => 'Panoramas',
			'735' => 'Perspective',
			'736' => 'Sports',
			'737' => 'A Sense of Place',
			'738' => 'Tracking',
			'739' => 'Tracking',
			'740' => 'Tracking',
			'741' => 'Tracking',
			'742' => 'Tracking',
			'743' => 'Tracking',
			'744' => 'Features',
			'745' => 'Features',
			'746' => 'Features',
			'747' => 'Essay',
			'748' => 'Panoramas',
			'749' => 'Panoramas',
			'750' => 'Panoramas',
			'751' => 'Panoramas',
			'752' => 'Panoramas',
			'753' => 'Panoramas',
			'754' => 'First Words',
			'755' => 'Letters',
			'756' => 'Short subject',
			'757' => 'Sports',
			'758' => 'In Season',
			'759' => 'Last Words',
			'760' => 'Tracking',
			'761' => 'Tracking',
			'762' => 'Tracking',
			'763' => 'Features',
			'764' => 'Features',
			'765' => 'Features',
			'766' => 'Features',
			'767' => 'Essay',
			'768' => 'Panoramas',
			'769' => 'Panoramas',
			'770' => 'Panoramas',
			'771' => 'Panoramas',
			'772' => 'Panoramas',
			'773' => 'Sports',
			'774' => 'First Words',
			'775' => 'In Season',
			'776' => 'Letters',
			'777' => 'Sports',
			'778' => 'Last Words',
			'779' => 'Tracking',
			'780' => 'Tracking',
			'781' => 'Tracking',
			'782' => 'Tracking',
			'783' => 'Short subject',
			'784' => 'Panoramas',
			'785' => 'Panoramas',
			'786' => 'Features',
			'787' => 'Features',
			'788' => 'Features',
			'789' => 'Features',
			'790' => 'Essay',
			'791' => 'Panoramas',
			'792' => 'Panoramas',
			'793' => 'Panoramas',
			'794' => 'Panoramas',
			'795' => 'First Words',
			'796' => 'Letters',
			'797' => 'Sports',
			'798' => 'In Season',
			'799' => 'Last Words',
			'800' => 'Tracking',
			'801' => 'Tracking',
			'802' => 'Tracking',
			'803' => 'Features',
			'804' => 'Features',
			'805' => 'Features',
			'806' => 'Features',
			'807' => 'Essay',
			'808' => 'Panoramas',
			'809' => 'Panoramas',
			'810' => 'Panoramas',
			'811' => 'Panoramas',
			'812' => 'Panoramas',
			'813' => 'Panoramas',
			'814' => 'Panoramas',
			'815' => 'First Words',
			'816' => 'Sports',
			'817' => 'In Season',
			'818' => 'Last Words',
			'819' => 'Tracking',
			'820' => 'Tracking',
			'821' => 'Tracking',
			'822' => 'Tracking',
			'823' => 'Features',
			'824' => 'Features',
			'825' => 'Features',
			'826' => 'Panoramas',
			'827' => 'Essay',
			'828' => 'Panoramas',
			'829' => 'Panoramas',
			'830' => 'Panoramas',
			'831' => 'Panoramas',
			'832' => 'First Words',
			'833' => 'Letters',
			'834' => 'Short subject',
			'835' => 'Sports',
			'836' => 'In Season',
			'837' => 'Last Words',
			'838' => 'Tracking',
			'839' => 'Tracking',
			'840' => 'Tracking',
			'841' => 'WSU Alumni Association News',
			'842' => 'Features',
			'843' => 'Features',
			'844' => 'Features',
			'845' => 'Essay',
			'846' => 'Panoramas',
			'847' => 'Panoramas',
			'848' => 'Panoramas',
			'849' => 'Panoramas',
			'850' => 'Panoramas',
			'851' => 'Panoramas',
			'852' => 'Letters',
			'853' => 'First Words',
			'854' => 'Sports',
			'855' => 'Sports',
			'856' => 'In Season',
			'857' => 'Last Words',
			'858' => 'Tracking',
			'859' => 'Tracking',
			'860' => 'Tracking',
			'861' => 'WSU Alumni Association News',
			'862' => 'Features',
			'863' => 'Features',
			'864' => 'Features',
			'865' => 'Panoramas',
			'866' => 'Panoramas',
			'867' => 'Panoramas',
			'868' => 'Panoramas',
			'869' => 'Panoramas',
			'870' => 'Panoramas',
			'871' => 'First Words',
			'872' => 'Letters',
			'873' => 'Short subject',
			'874' => 'Sports',
			'875' => 'In Season',
			'876' => 'Last Words',
			'877' => 'Tracking',
			'878' => 'Tracking',
			'879' => 'Tracking',
			'880' => 'Tracking',
			'881' => 'WSU Alumni Association News',
			'882' => 'Features',
			'883' => 'Features',
			'884' => 'Features',
			'885' => 'Panoramas',
			'886' => 'Panoramas',
			'887' => 'Panoramas',
			'888' => 'Panoramas',
			'889' => 'Panoramas',
			'890' => 'Panoramas',
			'891' => 'Panoramas',
			'892' => 'First Words',
			'893' => 'Letters',
			'894' => 'Sports',
			'895' => 'In Season',
			'896' => 'Last Words',
			'897' => 'Tracking',
			'898' => 'Tracking',
			'899' => 'Tracking',
			'900' => 'WSU Alumni Association News',
			'901' => 'First Words',
			'902' => 'Letters',
			'903' => 'Letters',
			'904' => 'Panoramas',
			'905' => 'Panoramas',
			'906' => 'Panoramas',
			'907' => 'Sports',
			'908' => 'In Season',
			'909' => 'Panoramas',
			'910' => 'Panoramas',
			'911' => 'Panoramas',
			'912' => 'Panoramas',
			'913' => 'Features',
			'914' => 'Features',
			'915' => 'Essay',
			'916' => 'Features',
			'917' => 'Tracking',
			'918' => 'Tracking',
			'919' => 'Tracking',
			'920' => 'WSU Alumni Association News',
			'921' => 'Last Words',
			'922' => 'First Words',
			'923' => 'Departments',
			'924' => 'Panoramas',
			'925' => 'Panoramas',
			'926' => 'Panoramas',
			'927' => 'Panoramas',
			'928' => 'Short subject',
			'929' => 'Panoramas',
			'930' => 'Panoramas',
			'931' => 'Panoramas',
			'932' => 'Panoramas',
			'933' => 'Sports',
			'934' => 'In Season',
			'935' => 'Features',
			'936' => 'Features',
			'937' => 'Features',
			'938' => 'Tracking',
			'939' => 'Tracking',
			'940' => 'Tracking',
			'941' => 'Tracking',
			'942' => 'Tracking',
			'943' => 'WSU Alumni Association News',
			'944' => 'Last Words',
			'945' => 'First Words',
			'946' => 'Posts',
			'947' => 'Panoramas',
			'948' => 'Panoramas',
			'949' => 'Panoramas',
			'950' => 'Short subject',
			'951' => 'Sports',
			'952' => 'Panoramas',
			'953' => 'Panoramas',
			'954' => 'Panoramas',
			'955' => 'In Season',
			'956' => 'Features',
			'957' => 'Features',
			'958' => 'Features',
			'959' => 'Features',
			'960' => 'Tracking',
			'961' => 'Tracking',
			'962' => 'Tracking',
			'963' => 'WSU Alumni Association News',
			'964' => 'Last Words',
			'965' => 'First Words',
			'966' => 'Posts',
			'967' => 'Panoramas',
			'968' => 'Panoramas',
			'969' => 'Panoramas',
			'970' => 'Panoramas',
			'971' => 'Panoramas',
			'972' => 'Sports',
			'973' => 'In Season',
			'974' => 'Panoramas',
			'975' => 'Panoramas',
			'976' => 'Panoramas',
			'977' => 'Features',
			'978' => 'Features',
			'979' => 'Features',
			'980' => 'Features',
			'981' => 'Tracking',
			'982' => 'Tracking',
			'983' => 'Tracking',
			'984' => 'Tracking',
			'985' => 'WSU Alumni Association News',
			'986' => 'Last Words',
			'987' => 'First Words',
			'988' => 'Posts',
			'989' => 'Panoramas',
			'990' => 'Panoramas',
			'991' => 'Panoramas',
			'992' => 'Panoramas',
			'993' => 'Short subject',
			'994' => 'Panoramas',
			'995' => 'In Season',
			'996' => 'Sports',
			'997' => 'Features',
			'998' => 'Features',
			'999' => 'Essay',
			'1000' => 'Features',
			'1001' => 'Tracking',
			'1002' => 'Tracking',
			'1003' => 'Tracking',
			'1004' => 'WSU Alumni Association News',
			'1005' => 'Last Words',
			'1006' => 'First Words',
			'1007' => 'Posts',
			'1008' => 'Panoramas',
			'1009' => 'Panoramas',
			'1010' => 'Panoramas',
			'1011' => 'Panoramas',
			'1012' => 'In Season',
			'1013' => 'Sports',
			'1014' => 'Sports',
			'1015' => 'Panoramas',
			'1016' => 'Panoramas',
			'1017' => 'Features',
			'1018' => 'Features',
			'1019' => 'Essay',
			'1020' => 'Features',
			'1021' => 'Tracking',
			'1022' => 'Tracking',
			'1023' => 'Tracking',
			'1024' => 'Tracking',
			'1025' => 'WSU Alumni Association News',
			'1026' => 'Last Words',
			'1027' => 'First Words',
			'1028' => 'Posts',
			'1029' => 'Panoramas',
			'1030' => 'Panoramas',
			'1031' => 'Panoramas',
			'1032' => 'Panoramas',
			'1033' => 'Sports',
			'1034' => 'In Season',
			'1035' => 'Panoramas',
			'1036' => 'Panoramas',
			'1037' => 'Features',
			'1038' => 'Features',
			'1039' => 'Features',
			'1040' => 'Tracking',
			'1041' => 'Tracking',
			'1042' => 'Tracking',
			'1043' => 'Tracking',
			'1044' => 'WSU Alumni Association News',
			'1045' => 'Last Words',
			'1046' => 'First Words',
			'1047' => 'Posts',
			'1048' => 'Panoramas',
			'1049' => 'Panoramas',
			'1050' => 'Panoramas',
			'1051' => 'Panoramas',
			'1052' => 'Panoramas',
			'1053' => 'Short subject',
			'1054' => 'Sports',
			'1055' => 'Sports',
			'1056' => 'In Season',
			'1057' => 'Panoramas',
			'1058' => 'Panoramas',
			'1059' => 'Panoramas',
			'1060' => 'Features',
			'1061' => 'Features',
			'1062' => 'Features',
			'1063' => 'Tracking',
			'1064' => 'Tracking',
			'1065' => 'Tracking',
			'1066' => 'Tracking',
			'1067' => 'WSU Alumni Association News',
			'1068' => 'Last Words',
			'1069' => 'First Words',
			'1070' => 'Features',
			'1071' => 'Posts',
			'1072' => 'Panoramas',
			'1073' => 'Panoramas',
			'1074' => 'Panoramas',
			'1075' => 'Sports',
			'1076' => 'Short subject',
			'1077' => 'Panoramas',
			'1078' => 'Panoramas',
			'1079' => 'Panoramas',
			'1080' => 'In Season',
			'1081' => 'Features',
			'1082' => 'Features',
			'1083' => 'Features',
			'1084' => 'Tracking',
			'1085' => 'Tracking',
			'1086' => 'Tracking',
			'1087' => 'WSU Alumni Association News',
			'1088' => 'First Words',
			'1089' => 'Posts',
			'1090' => 'Panoramas',
			'1091' => 'Panoramas',
			'1092' => 'Panoramas',
			'1093' => 'Panoramas',
			'1094' => 'Panoramas',
			'1095' => 'Sports',
			'1096' => 'Panoramas',
			'1097' => 'Panoramas',
			'1098' => 'In Season',
			'1099' => 'Features',
			'1100' => 'Features',
			'1101' => 'Essay',
			'1102' => 'Features',
			'1103' => 'Features',
			'1104' => 'Tracking',
			'1105' => 'Tracking',
			'1106' => 'Tracking',
			'1107' => 'WSU Alumni Association News',
			'1108' => 'Last Words',
			'1109' => 'First Words',
			'1110' => 'Posts',
			'1111' => 'Panoramas',
			'1112' => 'Panoramas',
			'1113' => 'Panoramas',
			'1114' => 'Panoramas',
			'1115' => 'In Season',
			'1116' => 'Panoramas',
			'1117' => 'Panoramas',
			'1118' => 'Panoramas',
			'1119' => 'Sports',
			'1120' => 'Features',
			'1121' => 'Features',
			'1122' => 'Features',
			'1123' => 'Tracking',
			'1124' => 'Tracking',
			'1125' => 'Tracking',
			'1126' => 'Tracking',
			'1127' => 'WSU Alumni Association News',
			'1128' => 'Last Words',
			'1129' => 'First Words',
			'1130' => 'Posts',
			'1131' => 'Panoramas',
			'1132' => 'Panoramas',
			'1133' => 'Panoramas',
			'1134' => 'Panoramas',
			'1135' => 'Sports',
			'1136' => 'Panoramas',
			'1137' => 'Panoramas',
			'1138' => 'Panoramas',
			'1139' => 'In Season',
			'1140' => 'Features',
			'1141' => 'Features',
			'1142' => 'Features',
			'1143' => 'Tracking',
			'1144' => 'Tracking',
			'1145' => 'Tracking',
			'1146' => 'Tracking',
			'1147' => 'WSU Alumni Association News',
			'1148' => 'Last Words',
			'1149' => 'First Words',
			'1150' => 'Posts',
			'1151' => 'Panoramas',
			'1152' => 'Panoramas',
			'1153' => 'Panoramas',
			'1154' => 'Sports',
			'1155' => 'Panoramas',
			'1156' => 'Panoramas',
			'1157' => 'Panoramas',
			'1158' => 'In Season',
			'1159' => 'Features',
			'1160' => 'Features',
			'1161' => 'Features',
			'1162' => 'Features',
			'1163' => 'Tracking',
			'1164' => 'Tracking',
			'1165' => 'Tracking',
			'1166' => 'WSU Alumni Association News',
			'1167' => 'Ask Dr. Universe',
			'1168' => 'Last Words',
			'1169' => 'First Words',
			'1170' => 'Posts',
			'1171' => 'Panoramas',
			'1172' => 'Panoramas',
			'1173' => 'Panoramas',
			'1174' => 'Sports',
			'1175' => 'Sports',
			'1176' => 'Panoramas',
			'1177' => 'Panoramas',
			'1178' => 'Panoramas',
			'1179' => 'In Season',
			'1180' => 'Features',
			'1181' => 'Features',
			'1182' => 'Essay',
			'1183' => 'Features',
			'1184' => 'Features',
			'1185' => 'Tracking',
			'1186' => 'Tracking',
			'1187' => 'Tracking',
			'1188' => 'Tracking',
			'1189' => 'WSU Alumni Association News',
			'1190' => 'Ask Dr. Universe',
			'1191' => 'Last Words',
			'1192' => 'First Words',
			'1193' => 'Posts',
			'1194' => 'Panoramas',
			'1195' => 'Panoramas',
			'1196' => 'Short subject',
			'1197' => 'Panoramas',
			'1198' => 'Panoramas',
			'1199' => 'Panoramas',
			'1200' => 'Panoramas',
			'1201' => 'Sports',
			'1202' => 'In Season',
			'1203' => 'Features',
			'1204' => 'Features',
			'1205' => 'Essay',
			'1206' => 'Features',
			'1207' => 'Tracking',
			'1208' => 'Tracking',
			'1209' => 'Tracking',
			'1210' => 'Tracking',
			'1211' => 'WSU Alumni Association News',
			'1212' => 'Ask Dr. Universe',
			'1213' => 'Last Words',
		);

		foreach ( $section_data as $k => $v ) {
			$results = $wpdb->get_results( "SELECT $wpdb->posts.id FROM $wpdb->postmeta LEFT JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.id WHERE $wpdb->postmeta.meta_key = '_magazine_article_id' AND $wpdb->postmeta.meta_value = $k" );
			if ( isset( $results[0] ) && isset( $results[0]->id ) ) {
				$post_id = absint( $results[0]->id );
				wp_set_object_terms( $post_id, $v, 'wsu_magazine_section' );
			}
		}

		WP_CLI::line( 'Done.' );
	}
}
