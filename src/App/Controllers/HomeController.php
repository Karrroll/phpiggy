<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\TemplateEngine;
use App\Services\TransactionService;

class HomeController {
  public function __construct(private TemplateEngine $view, private TransactionService $transactionService) {
  }

  public function home() {
    //set transactions limit parameters displayed on the page
    $page = max(1, (int) ($_GET['p'] ?? 1));
    $length = 3;
    $offset = ($page - 1) * $length;

    $searchTerm = $_GET['s'] ?? NULL;

    [$transactions, $count] = $this->transactionService->getUserTransactions($length, $offset);
    
    $lastPage = ceil($count / $length);

    //if $lastPage = 3 crate $pages array from 1 to 3, else empty array etc.
    $pages = $lastPage ? range(1, $lastPage) : [];
    //array_map() -> for each $pages element do fn()
    //example $pageLinks result: ['p=1&s=search_value', 'p=2&s=search_value', 'p=3&s=search_value']
    $pageLinks = array_map(
      fn($pageNum) => http_build_query([
        'p' => $pageNum,
        's' => $searchTerm
      ]),
      $pages
    );

    echo $this->view->render(
      "index.php",
      [
        'transactions' => $transactions,
        'currentPage' => $page,
        //convert data to URL query string "p=value&s=value", and use it in the href attribute to create e.g. home page link with GET parameters
        'previousPageQuery' => http_build_query([
          'p' => $page - 1,
          's' => $searchTerm
        ]),
        'lastPage' => $lastPage,
        //convert data to URL query string "p=value&s=value", and use it in the href attribute to create e.g. home page link with GET parameters
        'nextPageQuery' => http_build_query([
          'p' => $page + 1,
          's' => $searchTerm
        ]),
        'pageLinks' => $pageLinks,
        'searchTerm' => $searchTerm
      ]
    );
  }
}