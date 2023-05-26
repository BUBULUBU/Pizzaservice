<?php declare(strict_types=1);
// UTF-8 marker äöüÄÖÜß€
/**
 * Class PageTemplate for the exercises of the EWA lecture
 * Demonstrates use of PHP including class and OO.
 * Implements Zend coding standards.
 * Generate documentation with Doxygen or phpdoc
 *
 * PHP Version 7.4
 *
 * @file     PageTemplate.php
 * @package  Page Templates
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 * @version  3.1
 */

// to do: change name 'PageTemplate' throughout this file
require_once './Page.php';

/**
 * This is a template for top level classes, which represent
 * a complete web page and which are called directly by the user.
 * Usually there will only be a single instance of such a class.
 * The name of the template is supposed
 * to be replaced by the name of the specific HTML page e.g. baker.
 * The order of methods might correspond to the order of thinking
 * during implementation.
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 */
class Driver extends Page
{
    // to do: declare reference variables for members 
    // representing substructures/blocks

    /**
     * Instantiates members (to be defined above).
     * Calls the constructor of the parent i.e. page class.
     * So, the database connection is established.
     * @throws Exception
     */
    protected function __construct()
    {
        parent::__construct();
        // to do: instantiate members representing substructures/blocks
    }

    /**
     * Cleans up whatever is needed.
     * Calls the destructor of the parent i.e. page class.
     * So, the database connection is closed.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Fetch all data that is necessary for later output.
     * Data is returned in an array e.g. as associative array.
	 * @return array An array containing the requested data. 
	 * This may be a normal array, an empty array or an associative array.
     */
    protected function getViewData():array
    {
        //$query = "SELECT oa.ordering_id, a.name, oa.status, o.address, a.price FROM ordered_article oa LEFT JOIN article a ON oa.article_id = a.article_id LEFT JOIN ordering o ON o.ordering_id = oa.ordering_id WHERE oa.status >= 2 AND oa.status < 4";
        $query = "SELECT ordered_article.ordered_article_id, ordered_article.ordering_id, MIN(status), ordering.address,ordering.ordering_time, SUM(article.price) FROM ordered_article JOIN ordering ON ordered_article.ordering_id = ordering.ordering_id JOIN article ON ordered_article.article_id = article.article_id GROUP BY ordered_article.ordering_id ORDER BY ordering_id";

        if($result = $this->_database->query($query)) {
            $dbArray = $result->fetch_all(MYSQLI_ASSOC);
            $result->free(); // Clear DB Record Set

            return $dbArray;
        }

        return array();
    }

    /**
     * First the required data is fetched and then the HTML is
     * assembled for output. i.e. the header is generated, the content
     * of the page ("view") is inserted and -if available- the content of
     * all views contained is generated.
     * Finally, the footer is added.
	 * @return void
     */
    protected function generateView():void
    {
		$data = $this->getViewData();
        $this->generatePageHeader('Fahrer', '', true); //to do: set optional parameters

        echo "<h1>Fahrer Seite</h1>";

        // Debugging
        // echo "<pre>" , print_r($data) , "</pre>";

        // Filter array
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['MIN(status)'] != 2 && $data[$i]['MIN(status)'] != 3) {
                \array_splice($data, $i, 1);
                $i -= 1;
            }
        }

        if(count($data) <= 0) {
            echo "<p>Es sind keine Lieferungen vorhanden!</p>";
        }

        foreach($data as $v) {
            $oid = $v["ordering_id"];
            $id = $v["ordered_article_id"];
            $status = $v["MIN(status)"];
            $address = htmlspecialchars($v["address"]);
            $totalPrice = number_format(floatval($v["SUM(article.price)"]), 2, ',', ' ');

            $status == 2 ? $_checkedDone = 'checked' : $_checkedDone = '';
            $status == 3 ? $_checkedDelivering = 'checked' : $_checkedDelivering = '';
            $status == 4 ? $_checkedDelivered = 'checked' : $_checkedDelivered = '';

            echo <<<EOT
            <fieldset>
                <form action="#" method="POST" accept-charset="UTF-8">
                    <p>Bestellung #$oid (Betrag: $totalPrice €)</p>
                    <p>Anschrift: $address</p>
                    <input type='radio' onchange='this.form.submit();' id='radioDone$id' name='status' value='2' $_checkedDone>
                    <label for='radioDone$id'>Bereit zur Auslieferung</label>
                    <input type='radio' onchange='this.form.submit();' id='radioDelivering$id' name='status' value='3' $_checkedDelivering>
                    <label for='radioDelivering$id'>Wird ausgeliefert</label>
                    <input type='radio' onchange='this.form.submit();' id='radioDelivered$id' name='status' value='4' $_checkedDelivered>
                    <label for='radioDelivered$id'>Ausgeliefert</label>
                    <input type='hidden' name='id' value='$oid' />
                </form>
            </fieldset>

            <br />
            EOT;
        }

        $this->generatePageFooter();
    }

    /**
     * Processes the data that comes via GET or POST.
     * If this page is supposed to do something with submitted
     * data do it here.
	 * @return void
     */
    protected function processReceivedData():void
    {
        parent::processReceivedData();

        if(count($_POST)) {
            if(isset($_POST["status"]) & isset($_POST["id"])) {
                $status = $_POST["status"];
                $id = $_POST["id"];

                $stmt = $this->_database->prepare("UPDATE ordered_article SET status = (?) WHERE ordering_id = (?)");
                $stmt->bind_param("ii", $status, $id);
                $stmt->execute();

                header("HTTP/1.1 303 See Other");
                header("Location: fahrer.php");
                die();
            }
        }
    }

    /**
     * This main-function has the only purpose to create an instance
     * of the class and to get all the things going.
     * I.e. the operations of the class are called to produce
     * the output of the HTML-file.
     * The name "main" is no keyword for php. It is just used to
     * indicate that function as the central starting point.
     * To make it simpler this is a static function. That is you can simply
     * call it without first creating an instance of the class.
	 * @return void
     */
    public static function main():void
    {
        try {
            $page = new Driver();
            $page->processReceivedData();
            $page->generateView();
        } catch (Exception $e) {
            //header("Content-type: text/plain; charset=UTF-8");
            header("Content-type: text/html; charset=UTF-8");
            echo $e->getMessage();
        }
    }
}

// This call is starting the creation of the page. 
// That is input is processed and output is created.
Driver::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >