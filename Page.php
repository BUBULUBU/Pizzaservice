<?php declare(strict_types=1);
// UTF-8 marker äöüÄÖÜß€
header("Content-type: text/html; charset=UTF-8");
/**
 * Class Page for the exercises of the EWA lecture
 * Demonstrates use of PHP including class and OO.
 * Implements Zend coding standards.
 * Generate documentation with Doxygen or phpdoc
 *
 * PHP Version 7.4
 *
 * @file     Page.php
 * @package  Page Templates
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 * @version  3.1
 */

/**
 * This abstract class is a common base class for all
 * HTML-pages to be created.
 * It manages access to the database and provides operations
 * for outputting header and footer of a page.
 * Specific pages have to inherit from that class.
 * Each derived class can use these operations for accessing the database
 * and for creating the generic parts of a HTML-page.
 *
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 */
abstract class Page
{
    // --- ATTRIBUTES ---

    /**
     * Reference to the MySQLi-Database that can be used
     * by all operations of the class or inherited classes.
     */
    protected MySQLi $_database;

    // --- OPERATIONS ---

    /**
     * Connects to DB and stores
     * the connection in member $_database.
     * Needs name of DB, user, password.
     */
    protected function __construct()
    {
        error_reporting(E_ALL);

        $host = "localhost";
        /********************************************/
        // This code switches from the the local installation (XAMPP) to the docker installation 
        if (gethostbyname('mariadb') != "mariadb") { // mariadb is known?
            $host = "mariadb";
        }
        /********************************************/

        $this->_database = new MySQLi($host, "public", "public", "pizzaservice");

        if (mysqli_connect_errno()) {
            throw new Exception("Connect failed: " . mysqli_connect_error());
        }

        // set charset to UTF8!!
        if (!$this->_database->set_charset("utf8")) {
            throw new Exception($this->_database->error);
        }
    }

    /**
     * Closes the DB connection and cleans up
     */
    public function __destruct()
    {
        $this->_database->close();
    }

    /**
     * Generates the header section of the page.
     * i.e. starting from the content type up to the body-tag.
     * Takes care that all strings passed from outside
     * are converted to safe HTML by htmlspecialchars.
     *
     * @param string $title $title is the text to be used as title of the page
     * @param string $jsFile path to a java script file to be included, default is "" i.e. no java script file
     * @param bool $autoreload  true: auto reload the page every 5 s, false: not auto reload
     * @return void
     */
    protected function generatePageHeader(string $title = "", string $jsFile = "", bool $autoreload = false):void
    {
        $title = htmlspecialchars($title);
        header("Content-type: text/html; charset=UTF-8;");

        $nav_id_names[] = ["id" => "bestellung.php", "name" => "Bestellung"];
        $nav_id_names[] = ["id" => "baecker.php", "name" => "Bäcker"];
        $nav_id_names[] = ["id" => "fahrer.php", "name" => "Fahrer"];
        $nav_id_names[] = ["id" => "kunde.php", "name" => "Kunde"];

        if($autoreload) {
            header("Refresh: 5;");
        }

        $scriptFile = "";
        if($jsFile) {
            $scriptFile = "<script src='$jsFile'></script>";
        }

        echo <<<EOT
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title>$title</title>
            <script src='Script.js'></script>
            $scriptFile
            <link href="https://fonts.googleapis.com/css?family=Mouse+Memoirs:400,700" rel="stylesheet">
            <link href="style.css" rel="stylesheet">
        </head>
        <body>
            <section class="topnav" id="navigation">
        EOT;

        foreach($nav_id_names as $nav) {
            echo "<a href='$nav[id]'>$nav[name]</a>";
        }

        echo <<<EOT
                <a href="#" class="bars" onclick="collapseNav()">
                    <img src="bars.png" alt="bars" width="16" height="16">
                </a>
            </section>
            <section class="main-body">
        EOT;

        // This is required as the navbar ist created after the real initialization of the js file
        // and we do not find then the elements of it
        echo "<script>updateNavBar()</script>";
    }

    /**
     * Outputs the end of the HTML-file i.e. </body> etc.
	 * @return void
     */
    protected function generatePageFooter():void
    {
        echo<<<EOT
            <footer>
                <section class="imprint">
                    <hr>
                    <h2>Impressum</h2>
                    <p> EWA Pizza  <br/> Schöfferstraße 36 <br/> 64295 Darmstadt <br/> info@EWAPizza.com</p>
                </section>
            </footer>
        </section>
    </body>
</html>
EOT;
    }

    /**
     * Processes the data that comes in via GET or POST.
     * If every derived page is supposed to do something common
	 * with submitted data do it here. 
	 * E.g. checking the settings of PHP that
     * influence passing the parameters (e.g. magic_quotes).
	 * @return void
     */
    protected function processReceivedData():void
    {

    }
} // end of class

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >