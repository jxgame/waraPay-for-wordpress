<?php

class waraPay_db
{

    //private $wpdb;
    private $al_chr_clt;

    private $al_prefix;

    private $tbls = array('products', 'orders', 'templates');

    function __construct()
    {
        include_once(ABSPATH . '/wp-admin/includes/upgrade.php');
        global $wpdb;
        if ($wpdb->supports_collation()) {
            if (!empty($wpdb->charset)) {
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset ";
            }
            if (!empty($wpdb->collate)) {
                $charset_collate .= "COLLATE $wpdb->collate";
            }
        }
        //if( WPLANG == 'zh_CN')
        //$wpdb->query( "SET time_zone='+8:00';" );
        //$this->wpdb = $wpdb;
        $this->al_chr_clt = $charset_collate;
        $this->al_prefix  = $wpdb->prefix;
        //$wpdb->show_errors();
        $this->cdb_orders();
        $this->cdb_products();
        $this->cdb_templates();
        //$this->cdb__pro_meta();
        //$this->cdb__ord_meta();
        //$this->cdb__tpl_meta();
        $this->cdb__meta();
        //$this->update_pack_v2();
        $opt_name  = 'waraPay_update_pack';
        $opt_value = "2.0";
        if ($opt_value !== get_option($opt_name)) {
            $this->update_pack_v2();
            update_option($opt_name, $opt_value);
        }
    }

    function update_pack_v2()
    {
        global $wpdb;
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `proid` `proid` INT NOT NULL DEFAULT 0;");
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `series` `series` VARCHAR(20) NOT NULL DEFAULT '';");
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `aliacc` `aliacc` VARCHAR(100) NOT NULL DEFAULT 0;");
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `buynum` `buynum` SMALLINT NOT NULL DEFAULT 0;");
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `email` `email` VARCHAR(30) NOT NULL DEFAULT '';");
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `phone` `phone` VARCHAR(20) NOT NULL DEFAULT '';");
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `address` `address` VARCHAR(100) NOT NULL DEFAULT '';");
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `remarks` `remarks` VARCHAR(255) NOT NULL DEFAULT '';");
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `message` `message` VARCHAR(255) NOT NULL DEFAULT '';");
        $wpdb->query(
            "ALTER TABLE `$wpdb->orders` CHANGE `otime` `otime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';"
        );
        $wpdb->query(
            "ALTER TABLE `$wpdb->orders` CHANGE `stime` `stime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';"
        );
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `status` `status` SMALLINT NOT NULL DEFAULT 0;");
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `referer` `referer` VARCHAR(255) NOT NULL DEFAULT '';");
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `postcode` `postcode` VARCHAR(10) NOT NULL DEFAULT '';");
        $wpdb->query(
            "ALTER TABLE `$wpdb->orders` CHANGE `emailsend` `emailsend` BOOLEAN NOT NULL DEFAULT FALSE DEFAULT false;"
        );
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `ordname` `ordname` VARCHAR(20) NOT NULL DEFAULT '';");
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `ordfee` `ordfee` DOUBLE(10,2) NOT NULL DEFAULT '0.00';");
        $wpdb->query("ALTER TABLE `$wpdb->orders` CHANGE `sendsrc` `sendsrc` TEXT;");
        //-----------------------------------------------------------------------
        //
        //-----------------------------------------------------------------------
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `name` `name` VARCHAR(128) NOT NULL DEFAULT 'unkown Goods';");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `price` `price` DOUBLE(10,2) NOT NULL DEFAULT 0.01;");
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `discountb` `discountb` BOOLEAN NOT NULL DEFAULT FALSE;"
        );
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `discount` `discount` FLOAT(3,2) NOT NULL DEFAULT '0.85';"
        );
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `num` `num` INT NOT NULL DEFAULT '99999';");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `description` `description` TEXT;");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `images` `images` TEXT;");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `service` `service` SMALLINT NOT NULL DEFAULT 0;");
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `download` `download` VARCHAR(255) NOT NULL DEFAULT '';"
        );
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `callback` `callback` VARCHAR(100) NOT NULL DEFAULT '';"
        );
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `conotice` `conotice` BOOLEAN NOT NULL DEFAULT FALSE;");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `csnotice` `csnotice` BOOLEAN NOT NULL DEFAULT TRUE;");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `sonotice` `sonotice` BOOLEAN NOT NULL DEFAULT FALSE;");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `ssnotice` `ssnotice` BOOLEAN NOT NULL DEFAULT TRUE;");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `categories` `categories` INT NOT NULL DEFAULT 0;");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `tags` `tags` VARCHAR(50) NOT NULL DEFAULT '';");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `freight` `freight` DOUBLE(8,2) NOT NULL DEFAULT 0;");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `spfre` `spfre` BOOLEAN NOT NULL DEFAULT FALSE;");
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `location` `location` VARCHAR(20) NOT NULL DEFAULT '';"
        );
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `atime` `atime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;"
        );
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `btime` `btime` DATETIME NOT NULL DEFAULT '2011-10-11 11:11:11';"
        );
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `etime` `etime` DATETIME NOT NULL DEFAULT '2111-11-11 11:11:11';"
        );
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `promote` `promote` BOOLEAN NOT NULL DEFAULT FALSE;");
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `probdate` `probdate` DATE NOT NULL DEFAULT '2011-11-11';"
        );
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `proedate` `proedate` DATE NOT NULL DEFAULT '2111-11-11';"
        );
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `protime` `protime` BOOLEAN NOT NULL DEFAULT FALSE;");
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `probtime` `probtime` TIME NOT NULL DEFAULT '00:00:00';"
        );
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `proetime` `proetime` TIME NOT NULL DEFAULT '23:59:59';"
        );
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `html` `html` TEXT;");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `tplid` `tplid` INT NOT NULL DEFAULT 1;");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `mailcontent` `mailcontent` TEXT;");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `snum` `snum` INT UNSIGNED NOT NULL DEFAULT 0;");
        $wpdb->query(
            "ALTER TABLE `$wpdb->products` CHANGE `weight` `weight` DOUBLE(6,2) UNSIGNED NOT NULL DEFAULT '0.99';"
        );
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `autosend` `autosend` BOOLEAN NOT NULL DEFAULT FALSE;");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `autosep` `autosep` VARCHAR(255) NOT NULL DEFAULT '';");
        $wpdb->query("ALTER TABLE `$wpdb->products` CHANGE `autosrc` `autosrc` TEXT;");
        //-----------------------------------------------------------------------
        //
        //-----------------------------------------------------------------------
        $wpdb->query(
            "ALTER TABLE `$wpdb->templates` CHANGE `tplname` `tplname` VARCHAR(20) NOT NULL DEFAULT 'unkown Templates';"
        );
        $wpdb->query(
            "ALTER TABLE `$wpdb->templates` CHANGE `tpldescription` `tpldescription` VARCHAR(255) NOT NULL DEFAULT 'Templates Description.';"
        );
        $wpdb->query("ALTER TABLE `$wpdb->templates` CHANGE `tpljs` `tpljs` TEXT;");
        $wpdb->query("ALTER TABLE `$wpdb->templates` CHANGE `tplcss` `tplcss` TEXT;");
        $wpdb->query("ALTER TABLE `$wpdb->templates` CHANGE `tplhtml` `tplhtml` TEXT;");
    }

    private function cdb_orders()
    {
        global $wpdb;
        $table_name = $wpdb->orders;
        $sql        = "CREATE TABLE $table_name (
			ordid INT NOT NULL AUTO_INCREMENT,
			proid INT NOT NULL DEFAULT 0,
			series VARCHAR(20) NOT NULL DEFAULT '',
			aliacc VARCHAR(100) NOT NULL DEFAULT '',
			buynum SMALLINT NOT NULL DEFAULT 0,
			email VARCHAR(30) NOT NULL DEFAULT '',
			phone VARCHAR(20) NOT NULL DEFAULT '',
			address VARCHAR(100) NOT NULL DEFAULT '',
			remarks VARCHAR(255) NOT NULL DEFAULT '',
			message VARCHAR(255) NOT NULL DEFAULT '',
			otime DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			stime DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			status SMALLINT NOT NULL DEFAULT 0,
			referer VARCHAR(255) NOT NULL DEFAULT '',
			postcode VARCHAR(10) NOT NULL DEFAULT '',
			emailsend BOOLEAN NOT NULL DEFAULT FALSE DEFAULT false,
			ordname VARCHAR(20) NOT NULL DEFAULT '',
			ordfee DOUBLE(10,2) NOT NULL DEFAULT '0.00',
			sendsrc TEXT,
			PRIMARY KEY (ordid))
			AUTO_INCREMENT = 100
			$this->al_chr_clt;
			";
        maybe_create_table($table_name, $sql);
    }

    private function cdb_products()
    {
        global $wpdb;
        $table_name = $wpdb->products;
        $sql        = "CREATE TABLE $table_name (
			proid INT NOT NULL AUTO_INCREMENT,
			name VARCHAR(128) NOT NULL DEFAULT 'Goods Name',
			price DOUBLE(10,2) NOT NULL DEFAULT 0.01,
			discountb BOOLEAN NOT NULL DEFAULT FALSE,
			discount FLOAT(3,2) NOT NULL DEFAULT '0.85',
			num INT NOT NULL DEFAULT '99999',
			description TEXT,
			images TEXT,
			service SMALLINT NOT NULL DEFAULT 0,
			download VARCHAR(255) NOT NULL DEFAULT '',
			callback VARCHAR(100) NOT NULL DEFAULT '',
			conotice BOOLEAN NOT NULL DEFAULT FALSE,
			csnotice BOOLEAN NOT NULL DEFAULT TRUE,
			sonotice BOOLEAN NOT NULL DEFAULT FALSE,
			ssnotice BOOLEAN NOT NULL DEFAULT TRUE,
			categories INT NOT NULL DEFAULT 0,
			tags VARCHAR(50) NOT NULL DEFAULT '',
			freight DOUBLE(8,2) NOT NULL DEFAULT 0,
			spfre BOOLEAN NOT NULL DEFAULT FALSE,
			location VARCHAR(20) NOT NULL DEFAULT '',
			atime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			btime DATETIME NOT NULL DEFAULT '2011-10-11 11:11:11',
			etime DATETIME NOT NULL DEFAULT '2111-11-11 11:11:11',
			promote BOOLEAN NOT NULL DEFAULT FALSE,
			probdate DATE NOT NULL DEFAULT '2011-11-11',
			proedate DATE NOT NULL DEFAULT '2111-11-11',
			protime BOOLEAN NOT NULL DEFAULT FALSE,
			probtime TIME NOT NULL DEFAULT '00:00:00',
			proetime TIME NOT NULL DEFAULT '23:59:59',
			html TEXT,
			tplid INT NOT NULL DEFAULT 101,	
			mailcontent TEXT,
			snum INT UNSIGNED NOT NULL DEFAULT 0,
			weight DOUBLE(6,2) UNSIGNED NOT NULL DEFAULT '0.99',
			autosend BOOLEAN NOT NULL DEFAULT FALSE,
			autosep VARCHAR(255) NOT NULL DEFAULT '',
			autosrc TEXT,
			PRIMARY KEY (proid))
			AUTO_INCREMENT = 100
			$this->al_chr_clt;
			";
        maybe_create_table($table_name, $sql);
    }

    private function cdb_templates()
    {
        global $wpdb;
        $table_name = $wpdb->templates;
        $sql        = "CREATE TABLE $table_name (
			tplid INT NOT NULL AUTO_INCREMENT,
			tplname VARCHAR(20) NOT NULL DEFAULT 'Templates Name',
			tpldescription VARCHAR(255) NOT NULL DEFAULT 'Templates Description',
			tpljs TEXT,
			tplcss TEXT,
			tplhtml TEXT, 
			PRIMARY KEY (tplid))
			$this->al_chr_clt;
			";
        maybe_create_table($table_name, $sql);
    }

    private function cdb__meta()
    {
        global $wpdb;
        foreach ($this->tbls as $v) {
            $meta_type  = $wpdb->{$v . 'metatype'};
            $table_name = $wpdb->{$v . 'meta'};
            $sql        = "CREATE TABLE $table_name (
			meta_id INT NOT NULL AUTO_INCREMENT,
			{$meta_type}_id INT NOT NULL DEFAULT 0,
			meta_key VARCHAR(255) NOT NULL DEFAULT '',
			meta_value LONGTEXT,
			PRIMARY KEY (meta_id))
			$this->al_chr_clt;
			";
            maybe_create_table($table_name, $sql);
            $wpdb->{$v . 'meta'} = $table_name;
        }
    }

    function getTimeZoneStr()
    {
        $gmt_offset = get_option('gmt_offset');
    }
}

$ali_db = new waraPay_db;


