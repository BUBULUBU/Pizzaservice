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
class Order extends Page
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
        $query = "SELECT * FROM article";

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
        $this->generatePageHeader('Interpizza', 'Utils.js');

        echo <<<EOT
        <section class="flex-container">
            <section class="card-container">
                <h2>Artikel</h2>
                <section class='cards'>
        EOT;

        foreach($data as $v) {
            $id = $v["article_id"];
            $name = $v["name"];
            $picture = $v["picture"];
            $price = floatval($v["price"]);
            $formattedPrice = number_format($price, 2);

            echo "<section class='card' onclick='addToCart(\"$id\", \"$name\", \"$price\")'>";
            echo "<section class='content-container'>";
            echo "<img src='$picture' alt='$name'>";
            echo "<h2 class='title'>$name</h2>";
            echo "<p class='subtitle'>$formattedPrice €</p>";
            echo "</section>";
            echo "</section>";
        }

        echo "</section>";
        echo "</section>";

        echo <<<EOT
                <section class="cart-container">
                    <h2>Warenkorb</h2>
                    <section class="cart-content">
                        <form action="bestellung.php" onsubmit='fetchCart()' method="POST" accept-charset="UTF-8">
                            <select tabindex="1" class="pizza-cart" id="pizzaCart" name="pizza_cart[]" multiple></select>
                        
                            <p>Gesamt Preis: <span id="total_price">0.00</span> €</p>
                            <input type="text" class="address" id="address" name="address" value="" oninput="checkInput()" placeholder="Ihre Adresse">
                            <input type="submit" class="button" id="order" value="Bestellen" disabled>
                            <input type="button" class="button" onclick="deleteCart()" value="Alle Löschen">
                            <input type="button" class="button" onclick="deletePizza()" value="Auswahl Löschen">
                        </form>
                    </section>
                </section>
            </section>
        EOT;

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
            if(isset($_POST["pizza_cart"]) && isset($_POST["address"])) {
                $pizzas = $_POST["pizza_cart"];
                $address = $_POST["address"];

                $stmt1 = $this->_database->prepare("INSERT INTO ordering (address) VALUES (?)");
                $stmt1->bind_param("s", $address);
                $stmt1->execute();
                $insertId = $stmt1->insert_id;

                if(!isset($_SESSION["user_pizzas"])) $_SESSION["user_pizzas"] = array();
                $_SESSION["user_pizzas"][] = $insertId;

                $stmt2 = $this->_database->prepare("INSERT INTO ordered_article (ordering_id, article_id, status) VALUES (?, ?, ?)");
                foreach($pizzas as $item) {
                    $status = 0; // bind_param only accepts variables

                    $stmt2->bind_param("iii", $insertId, $item, $status);
                    $stmt2->execute();
                }
            }

            header("HTTP/1.1 303 See Other");
            header("Location: kunde.php");
            die();
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
            session_start();

            $page = new Order();
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
Order::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >