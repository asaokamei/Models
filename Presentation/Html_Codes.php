<?php
require_once( dirname( __FILE__ ) . '/Html_Forms.php' );

// +----------------------------------------------------------------------+
class selDateJs extends Html_Select
{
	// selecting date by jQuery's datepicker
    /* -------------------------------------------------------- */
	function __construct( $name='date' )
	{
        $this->name    = $name;
        $this->style   = "TEXT";
		$this->size    = '14';
		$this->max     = '10';
		$this->default_items   = date( 'Y-m-d' );
        $this->add_head_option = "選択してください";
        $this->err_msg_empty   = "";
		$this->setIME( 'OFF' );
		$this->addClass( 'datepicker' );
	}
    /* -------------------------------------------------------- */
	function makeName( $value )
	{
		return fmt::slash_date( $value );
	}
    /* -------------------------------------------------------- */
	function splitValue( $value )
	{
		// split value into each forms.
		// overload this method if necessary. 
		$date = fmt::std_date( $value, $value );
		return explode( '-', $date );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selDateTime extends Html_Select
{
	// selecting date by jQuery's datepicker
    /* -------------------------------------------------------- */
	function __construct( $name='datetime' )
	{
        $this->name    = $name;
        $this->style   = "TEXT";
		$this->size    = '24';
		$this->max     = '20';
		$this->default_items   = date( 'Y-m-d H:i:s' );
        $this->err_msg_empty   = "";
		$this->setIME( 'OFF' );
	}
    /* -------------------------------------------------------- */
	function makeName( $value )
	{
		return fmt::slash_date( $value );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selDateDs extends Html_Divs
{
	// selecting date by drop-select
    /* -------------------------------------------------------- */
    function __construct( $name, $start_y=NULL, $end_y=NULL, $ime="OFF" )
	{
		if( !$name ) $name = 'date';
		$this->name = $name;
		
		$this->implode_with_div = FALSE;
		$this->divider = '-';
		$this->num_div = 3;
		$this->d_forms[] = new selYear(  "{$this->name}_y", $start_y, $end_y );
		$this->d_forms[] = new selMonth( "{$this->name}_m" );
		$this->d_forms[] = new selDay(   "{$this->name}_d" );
	}
    /* -------------------------------------------------------- */
	function makeName( $value )
	{
		return fmt::jpn_date( $value );
	}
    /* -------------------------------------------------------- */
	function splitValue( $value )
	{
		// split value into each forms.
		// overload this method if necessary. 
		$date = fmt::std_date( $value, $value );
		return explode( '-', $date );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selDateYM extends Html_Divs
{
	// selecting date by drop-select
    /* -------------------------------------------------------- */
    function __construct( $name, $start_y=NULL, $end_y=NULL, $ime="OFF" )
	{
		if( !$name ) $name = 'date';
		$this->name = $name;
		
		$this->implode_with_div = FALSE;
		$this->divider = '-';
		$this->num_div = 2;
		$this->default_items = '0000-00';
		$this->d_forms[] = new selYear(  "{$this->name}_y", $start_y, $end_y );
		$this->d_forms[] = new selMonth( "{$this->name}_m" );
	}
    /* -------------------------------------------------------- */
	function makeName( $value )
	{
		list( $y, $m ) = $this->splitValue( $value );
		return "{$y}年{$m}月";
	}
    /* -------------------------------------------------------- */
	function splitValue( $value )
	{
		// split value into each forms.
		// overload this method if necessary. 
		return explode( '-', $value );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selDurationYM extends selDateYM
{
    /* -------------------------------------------------------- */
    function __construct( $name, $start_y=NULL, $end_y=NULL, $ime="OFF" )
	{
		if( !$name ) $name = 'date';
		$this->name = $name;
		
		$this->default_items = '00-00';
		$this->implode_with_div = FALSE;
		$this->divider = '-';
		$this->num_div = 2;
		$this->default_items = '*-*';
		$this->d_forms[] = new selYear(  "{$this->name}_1", $start_y, $end_y );
		$this->d_forms[] = new selMonth( "{$this->name}_2", NULL, 0 );
	}
}

// +----------------------------------------------------------------------+
class selTimeDs extends Html_Divs
{
	// selecting time by drop-select
    /* -------------------------------------------------------- */
    function __construct( $name, $opt1=5, $opt2=10, $ime="OFF" )
	{
		if( !$name ) $name = 'time';
		$this->name = $name;
		
		$this->divider = ':';
		$this->num_div = 3;
		$this->d_forms[] = new selHour( "{$this->name}_1" );
		$this->d_forms[] = new selMin(  "{$this->name}_2", $opt1 );
		$this->d_forms[] = new selSec(  "{$this->name}_3", $opt2 );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selTime extends Html_Divs
{
	// selecting time by text-box
    /* -------------------------------------------------------- */
    function __construct( $name, $opt1=NULL, $opt2=NULL, $ime="OFF" )
	{
		if( !$name ) $name = 'time';
		$this->name = $name;
		
		$this->divider = ':';
		$this->num_div = 3;
		$this->d_forms[] = new htmlText( "{$this->name}_1", 3, NULL, 'OFF' );
		$this->d_forms[] = new htmlText( "{$this->name}_2", 3,    2, 'OFF' );
		$this->d_forms[] = new htmlText( "{$this->name}_3", 3,    2, 'OFF' );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selTimeHM extends Html_Divs
{
	// selecting time by text-box
    /* -------------------------------------------------------- */
    function __construct( $name, $opt1=NULL, $opt2=NULL, $ime="OFF" )
	{
		if( !$name ) $name = 'time';
		$this->name = $name;
		
		$this->divider = ':';
		$this->num_div = 2;
		$this->d_forms[] = new selHour( "{$this->name}_1", NULL, NULL, 'OFF' );
		$this->d_forms[] = new selMin(  "{$this->name}_2", NULL, NULL, 'OFF' );
	}
    /* -------------------------------------------------------- */
	function makeName( $value )
	{
		list( $h, $m ) = $this->splitValue( $value );
		return "{$h}時{$m}分";
	}
    /* -------------------------------------------------------- */
	function splitValue( $value )
	{
		// split value into each forms.
		// overload this method if necessary. 
		list( $h, $m, $s ) = explode( $this->divider, $value );
		return array( $h, $m );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selDate extends Html_Divs
{
	// selecting date by text-box
    /* -------------------------------------------------------- */
    function __construct( $name, $opt1=NULL, $opt2=NULL, $ime="OFF" )
	{
		if( !$name ) $name = 'date';
		$this->name = $name;
		
		$this->default_items   = date( 'Y-m-d' );
		$this->divider = '-';
		$this->num_div = 3;
		$this->d_forms[] = new htmlText( "{$this->name}_y", 5,   4, 'OFF' );
		$this->d_forms[] = new htmlText( "{$this->name}_m", 3,   2, 'OFF' );
		$this->d_forms[] = new htmlText( "{$this->name}_d", 3,   2, 'OFF' );
	}
    /* -------------------------------------------------------- */
	function splitValue( $value )
	{
		// split value into each forms.
		// overload this method if necessary. 
		$date = fmt::std_date( $value, $value );
		return explode( '-', $date );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selTel extends Html_Divs
{
	// text input for tel number (Japanese style)
    /* -------------------------------------------------------- */
    function __construct( $name, $opt1=NULL, $opt2=NULL, $ime="OFF" )
	{
		if( !$name ) $name = 'tel';
		$this->name = $name;
		
		$this->divider = '-';
		$this->num_div = 3;
		$this->d_forms[] = new htmlText( "{$this->name}_1", 4, NULL, 'OFF' );
		$this->d_forms[] = new htmlText( "{$this->name}_2", 6, NULL, 'OFF' );
		$this->d_forms[] = new htmlText( "{$this->name}_3", 6, NULL, 'OFF' );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
//  basic html form elements
// +----------------------------------------------------------------------+
class selYear extends Html_Select
{
    /* -------------------------------------------------------- */
    function __construct( $name, $start_y=NULL, $end_y=NULL, $ime="OFF" )
	{
		if( !$name ) $name = 'year';
		$this->name = $name;
		$this->style  = 'SELECT';
		
		$this->default_items   = date( 'Y' );
        $this->add_head_option = "--";
		if( !have_value( $start_y ) ) $start_y = 1942; //date( 'Y' ) - 1;
		if( !have_value( $end_y   ) ) $end_y   = date( 'Y' ) + 1;
		for( $year = $start_y; $year <= $end_y; $year ++ ) {
			$this->item_data[] = array(
				sprintf( '%4d', $year ),
				sprintf( '%4d',  $year )
			);
		}
	}
    /* -------------------------------------------------------- */
	function makeHtml( $value )
	{
		$html = parent::makeHtml( $value ) . '年';
		return $html;
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selNendo extends selYear
{
    /* -------------------------------------------------------- */
    function __construct( $name, $start_y=NULL, $end_y=NULL, $ime="OFF" ) {
		parent::__construct( $name, $start_y, $end_y, $ime );
	}
    /* -------------------------------------------------------- */
	function makeHtml( $value ) {
		$html = parent::makeHtml( $value ) . '度';
		return $html;
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selMonth extends Html_Select
{
    /* -------------------------------------------------------- */
    function __construct( $name, $default=NULL, $start=1, $ime="OFF" )
	{
		if( !$name ) $name = 'month';
		$this->name = $name;
		$this->style  = 'SELECT';
		
		$this->default_items   = date( 'm' );
        $this->add_head_option = "--";
		$end = $start + 11;
		for( $mon = $start; $mon <= $end; $mon ++ ) {
			$this->item_data[] = array(
				sprintf( '%02d', $mon ),
				sprintf( '%2d',  $mon )
			);
		}
	}
    /* -------------------------------------------------------- */
	function makeHtml( $value )
	{
		$html = parent::makeHtml( $value ) . '月';
		return $html;
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selDay extends Html_Select
{
    /* -------------------------------------------------------- */
    function __construct( $name, $opt1=NULL, $opt2=NULL, $ime="OFF" )
	{
		if( !$name ) $name = 'day';
		$this->name = $name;
		$this->style  = 'SELECT';
		
		$this->default_items   = date( 'd' );
        $this->add_head_option = "--";
		for( $day = 1; $day <= 31; $day ++ ) {
			$this->item_data[] = array(
				sprintf( '%02d', $day ),
				sprintf( '%2d',  $day )
			);
		}
	}
    /* -------------------------------------------------------- */
	function makeHtml( $value )
	{
		$html = parent::makeHtml( $value ) . '日';
		return $html;
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selHour extends Html_Select
{
    /* -------------------------------------------------------- */
    function __construct( $name, $opt1=NULL, $opt2=NULL, $ime="OFF" )
	{
		if( !$name ) $name = 'hour';
		$this->name = $name;
		$this->style  = 'SELECT';
		
		$this->default_items   = date( 'h' );
        $this->add_head_option = "--";
		for( $hour = 0; $hour <= 23; $hour ++ ) {
			$this->item_data[] = array(
				sprintf( '%02d', $hour ),
				sprintf( '%2d',  $hour )
			);
		}
	}
    /* -------------------------------------------------------- */
	function makeHtml( $value )
	{
		$html = parent::makeHtml( $value ) . '時';
		return $html;
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selMin extends Html_Select
{
    /* -------------------------------------------------------- */
    function __construct( $name, $intv=1, $opt2=NULL, $ime="OFF" )
	{
		if( !$name ) $name = 'minute';
		$this->name = $name;
		$this->style  = 'SELECT';
		
		$this->default_items   = date( 'm' );
        $this->add_head_option = "--";
		if( !$intv ) $intv = 1;
		for( $min = 0; $min <= 59; $min += $intv ) {
			$this->item_data[] = array(
				sprintf( '%02d', $min ),
				sprintf( '%2d',  $min )
			);
		}
	}
    /* -------------------------------------------------------- */
	function makeHtml( $value )
	{
		$html = parent::makeHtml( $value ) . '分';
		return $html;
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class selSec extends Html_Select
{
    /* -------------------------------------------------------- */
    function __construct( $name, $intv=1, $opt2=NULL, $ime="OFF" )
	{
		if( !$name ) $name = 'second';
		$this->name = $name;
		$this->style  = 'SELECT';
		
		$this->default_items   = date( 's' );
        $this->add_head_option = "--";
		if( !$intv ) $intv = 1;
		for( $sec = 0; $sec <= 59; $sec += $intv ) {
			$this->item_data[] = array(
				sprintf( '%02d', $sec ),
				sprintf( '%2d',  $sec )
			);
		}
	}
    /* -------------------------------------------------------- */
	function makeHtml( $value )
	{
		$html = parent::makeHtml( $value ) . '分';
		return $html;
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class htmlHidden extends Html_Select
{
    /* -------------------------------------------------------- */
	function __construct( $name, $opt1=NULL, $opt2=NULL, $ime='ON', $option=NULL )
	{
		if( WORDY > 3 ) echo "htmlHidden( $name, $size, $max, $ime, $option )";
		$this->name   = $name;
		$this->style  = 'HIDDEN';
		$this->setOption( $option );
        $this->err_msg_empty   = "";
    }
    /* -------------------------------------------------------- */
    function show( $type="NEW", $value="" )
    {
		if( in_array( $type, array( 'NEW', 'EDIT' ) ) ) {
			$type = 'PASS';
		}
		return parent::show( $type, $value );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class htmlPswd extends Html_Select
{
    /* -------------------------------------------------------- */
	function __construct( $name, $size=40, $max=NULL, $ime='OFF', $option=NULL )
	{
		if( WORDY > 3 ) echo "htmlText( $name, $size, $max, $ime, $option )";
		$this->name   = $name;
		$this->style  = 'PASSWORD';
		$this->size   = $size;
		$this->max    = $max;
		$this->setIME( $ime );
		$this->setOption( $option );
        $this->err_msg_empty   = "";
    }
    /* -------------------------------------------------------- */
	function makeName( $value )
	{
		return str_repeat( '*', strlen( $value ) );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class htmlText extends Html_Select
{
    /* -------------------------------------------------------- */
	function __construct( $name, $size=40, $max=NULL, $ime='ON', $option=NULL )
	{
		if( WORDY > 3 ) echo "htmlText( $name, $size, $max, $ime, $option )";
		$this->name   = $name;
		$this->style  = 'TEXT';
		$this->size   = $size;
		$this->max    = $max;
		$this->setIME( $ime );
		$this->setOption( $option );
        $this->err_msg_empty   = "";
    }
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class htmlTextArea extends Html_Select
{
    /* -------------------------------------------------------- */
	function __construct( $name=NULL, $width=40, $height=5, $ime='ON', $option=NULL )
	{
		if( WORDY > 3 ) echo "htmlTextArea( $name, $width, $height, $ime, $option )";
		$this->name   = $name;
		$this->style  = 'TEXTAREA';
		$this->width  = $width;
		$this->height = $height;
		$this->setIME( $ime );
		$this->setOption( $option );
        $this->err_msg_empty   = "";
    }
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
//  prefectures of Japan
// +----------------------------------------------------------------------+
class selPref extends Html_Select
{
    /* -------------------------------------------------------- */
    function __construct( $name="pref" )
    {
		$this->name            = $name;
        $this->style           = "SELECT";
        $this->add_head_option = "選択してください";
        $this->err_msg_empty   = "";
        $this->item_data       = get_pref_rawdata();
    }
    /* -------------------------------------------------------- */
}

class selRegion extends Html_Select
{
    /* -------------------------------------------------------- */
    function __construct( $name="region" )
    {
		$this->name            = $name;
        $this->style           = "SELECT";
        $this->add_head_option = "指定なし";
        $this->err_msg_empty   = "希望地指定なし";
        $this->item_data       = selPrefByRegion::get_region();
    }
	// +----------------------------------------------------------------------+
	function use_short_name()
	{
		$this->item_data = array(
			array( 'HK', '北海道' ),
			array( 'TH', '東北' ),
			array( 'KT', '首都圏' ),
			array( 'K2', '北関東/甲信越' ),
			array( 'KK', '近畿' ),
			array( 'TK', '東海' ),
			array( 'HR', '北陸' ),
			array( 'CH', '中国' ),
			array( 'SK', '四国' ),
			array( 'KO', '九州/沖縄' ),
			array( 'FR', '国外' ),
		);
    }
    /* -------------------------------------------------------- */
}

class selPrefByRegion extends Html_Select
{
    /* -------------------------------------------------------- */
    function __construct( $name="pref" )
    {
		$this->name            = $name;
        $this->style           = "SELECT";
        $this->add_head_option = "選択してください";
        $this->err_msg_empty   = "";
        $this->item_data       = $this->get_region();
	
		$this->html_append_func[] = 'sel_set_option';
		$this->sel_setopt_data  = $this->get_pref();
		$this->sel_setopt_target= $name;
    }
    /* -------------------------------------------------------- */
    function makeHtml( $value )
    {
		$name  = $this->name;
		
		$this->name .= '_ByRegion';
		$html  = $this->getSelect( $this->name, $this->item_data, $this->size, $value, $this->add_head_option );
		
		$html .= $this->sel_set_option();
		
		$this->name  = $name;
		$html .= $this->getSelect( $this->name, array(), $this->size, $value, '--' );
		return $html;
    }
	// +----------------------------------------------------------------------+
	function get_region()
	{
		return array( 
			array( 'HK', '北海道' ),
			array( 'TH', '東北' ),
			array( 'KT', '首都圏（東京、神奈川、千葉、埼玉）' ),
			array( 'K2', '首都圏以外の関東甲信越' ),
			array( 'KK', '近畿' ),
			array( 'TK', '東海' ),
			array( 'HR', '北陸' ),
			array( 'CH', '中国' ),
			array( 'SK', '四国' ),
			array( 'KO', '九州/沖縄' ),
			array( 'FR', '国外' ),
		);
    }
	// +----------------------------------------------------------------------+
	function get_region_from_pref( $pref )
	{
		$regions = self::get_pref();
		foreach( $regions as $reg_code => $pref_list ) {
			foreach( $pref_list as $pref_set ) {
				if( $pref_set[0] == $pref ) {
					return $reg_code;
				}
			}
		}
		return FALSE;
    }
	// +----------------------------------------------------------------------+
	function get_pref_from_region( $region )
	{
		$pref = array();
		$pref_arr = self::get_pref_list_from_region( $region );
		if( !empty( $pref_arr ) )
		foreach( $pref_arr as $pref_info ) {
			$pref[] = $pref_info[0];
		}
		if( WORDY > 3 ) wt( $pref, 'pref from region: ' . $region );
		return $pref;
    }
	// +----------------------------------------------------------------------+
	function get_pref_list_from_region( $region )
	{
		$pref = array();
		if( !have_value( $region ) ) {
		}
		else {
			$pref_list = self::get_pref();
			if( isset( $pref_list[ $region ] ) ) {
			$pref = $pref_list[ $region ];
			}
			else {
				$pref = array( // 国外
					array( "99"   ,  "国外" )
				); // それ以外は海外
			}

		}
		if( WORDY > 3 ) wt( $pref, 'pref list from region: ' . $region );
		return $pref;
    }
	// +----------------------------------------------------------------------+
	function get_pref()
	{
		return array(
			'HK' => array( // 北海道
				 array( "01"   ,  "北海道" ), 
			),
			'TH' => array( // 
				 array( "02"   ,  "青森県" ), 
				 array( "03"   ,  "岩手県" ), 
				 array( "04"   ,  "宮城県" ), 
				 array( "05"   ,  "秋田県" ), 
				 array( "06"   ,  "山形県" ), 
				 array( "07"   ,  "福島県" ), 
			),
			'KT' => array( // 首都圏（東京、神奈川、千葉、埼玉）
				 array( "11"   ,  "埼玉県" ), 
				 array( "12"   ,  "千葉県" ), 
				 array( "13"   ,  "東京都" ), 
				 array( "14"   ,  "神奈川県" ), 
			),
			'K2' => array( // 首都圏以外の関東甲信越
				 array( "08"   ,  "茨城県" ), 
				 array( "09"   ,  "栃木県" ), 
				 array( "10"   ,  "群馬県" ), 
				 array( "15"   ,  "新潟県" ), 
				 array( "19"   ,  "山梨県" ), 
				 array( "20"   ,  "長野県" ), 
			),
			'HR' => array( // 北陸
				 array( "16"   ,  "富山県" ), 
				 array( "17"   ,  "石川県" ), 
				 array( "18"   ,  "福井県" ), 
			),
			'KK' => array( // 近畿
				 array( "25"   ,  "滋賀県" ), 
				 array( "26"   ,  "京都府" ), 
				 array( "27"   ,  "大阪府" ), 
				 array( "28"   ,  "兵庫県" ), 
				 array( "29"   ,  "奈良県" ), 
				 array( "30"   ,  "和歌山県" ), 
			),
			'TK' => array( // 東海
				 array( "21"   ,  "岐阜県" ), 
				 array( "22"   ,  "静岡県" ), 
				 array( "23"   ,  "愛知県" ), 
				 array( "24"   ,  "三重県" ), 
			),
			'CH' => array( // 中国
				 array( "31"   ,  "鳥取県" ), 
				 array( "32"   ,  "島根県" ), 
				 array( "33"   ,  "岡山県" ), 
				 array( "34"   ,  "広島県" ), 
				 array( "35"   ,  "山口県" ), 
			),
			'SK' => array( // 四国
				 array( "36"   ,  "徳島県" ), 
				 array( "37"   ,  "香川県" ), 
				 array( "38"   ,  "愛媛県" ), 
				 array( "39"   ,  "高知県" ), 
			),
			'KO' => array( // 九州/沖縄
				 array( "40"   ,  "福岡県" ), 
				 array( "41"   ,  "佐賀県" ), 
				 array( "42"   ,  "長崎県" ), 
				 array( "43"   ,  "熊本県" ), 
				 array( "44"   ,  "大分県" ), 
				 array( "45"   ,  "宮崎県" ), 
				 array( "46"   ,  "鹿児島県" ), 
				 array( "47"   ,  "沖縄県" )
			),
			'FR' => array( // 国外
				array( "99"   ,  "国外" )
			),
		);
	}
		/* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
function get_label_pref( $val )
{
	return get_pref_name( $val );
}

// +----------------------------------------------------------------------+
function get_pref_name( $val )
{
	$pref = get_pref_rawdata();
	for( $i = 0; $i < count( $pref ); $i ++ ) {
		$key  = $pref[$i][0];
		$name = $pref[$i][1];
		if( $key === $val ) return $name;
	}
	return NULL;
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
function get_pref_rawdata()
{
	return array( 
		array( "01"   ,  "北海道" ), 
		array( "02"   ,  "青森県" ), 
		array( "03"   ,  "岩手県" ), 
		array( "04"   ,  "宮城県" ), 
		array( "05"   ,  "秋田県" ), 
		array( "06"   ,  "山形県" ), 
		array( "07"   ,  "福島県" ), 
		array( "08"   ,  "茨城県" ), 
		array( "09"   ,  "栃木県" ), 
		array( "10"   ,  "群馬県" ), 
		array( "11"   ,  "埼玉県" ), 
		array( "12"   ,  "千葉県" ), 
		array( "13"   ,  "東京都" ), 
		array( "14"   ,  "神奈川県" ), 
		array( "15"   ,  "新潟県" ), 
		array( "16"   ,  "富山県" ), 
		array( "17"   ,  "石川県" ), 
		array( "18"   ,  "福井県" ), 
		array( "19"   ,  "山梨県" ), 
		array( "20"   ,  "長野県" ), 
		array( "21"   ,  "岐阜県" ), 
		array( "22"   ,  "静岡県" ), 
		array( "23"   ,  "愛知県" ), 
		array( "24"   ,  "三重県" ), 
		array( "25"   ,  "滋賀県" ), 
		array( "26"   ,  "京都府" ), 
		array( "27"   ,  "大阪府" ), 
		array( "28"   ,  "兵庫県" ), 
		array( "29"   ,  "奈良県" ), 
		array( "30"   ,  "和歌山県" ), 
		array( "31"   ,  "鳥取県" ), 
		array( "32"   ,  "島根県" ), 
		array( "33"   ,  "岡山県" ), 
		array( "34"   ,  "広島県" ), 
		array( "35"   ,  "山口県" ), 
		array( "36"   ,  "徳島県" ), 
		array( "37"   ,  "香川県" ), 
		array( "38"   ,  "愛媛県" ), 
		array( "39"   ,  "高知県" ), 
		array( "40"   ,  "福岡県" ), 
		array( "41"   ,  "佐賀県" ), 
		array( "42"   ,  "長崎県" ), 
		array( "43"   ,  "熊本県" ), 
		array( "44"   ,  "大分県" ), 
		array( "45"   ,  "宮崎県" ), 
		array( "46"   ,  "鹿児島県" ), 
		array( "47"   ,  "沖縄県" ),
		array( "99"   ,  "国外" )
	);
}
        


?>