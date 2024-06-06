<?php

namespace Ajustatech\PrintService\Controllers;

use Illuminate\Http\Request;
use Ajustatech\PrintService\Contracts\CellRepositoryInterface as Cell;
use Ajustatech\PrintService\Contracts\RowRepositoryInterface as Row;
use Ajustatech\PrintService\Contracts\DocumentsRepositoryInterface as Document;


// use Illuminate\Routing\Controller;

class CouponController extends Controller
{
    protected $cell;
    protected $row;
    protected $document;

    public function __construct(Cell $cell, Row $row, Document $document) {
        $this->cell = $cell;
        $this->row = $row;
        $this->document = $document;
    }

    public function showCoupon()
    {


        $cell1 = $this->cell
            ->setBgColor('#ccc')
            ->setFontSize(12)
            ->setTextColor('#000')
            ->alignTextCenter()
            ->setWidth(25)
            ->setBorderStyle('top', '1px solid black')
            ->setBorderStyle('bottom', '1px solid black')
            ->setFontFamily('Arial');




        // // Criando linhas e adicionando células
        // $row1 = (new Row())->addCell($cell1)->addCell($cell2)->topSpacing(10)->bottomSpacing(5);
        // $row2 = (new Row())->addCell($cell3)->addCell($cell4)->topSpacing(5)->bottomSpacing(10);

        // // Criando o recibo e adicionando as linhas
        // $receipt = new Documents();
        // $receipt->addRow($row1);
        // $receipt->addRow($row2);

        // // Gerando HTML
        // $html = $receipt->generateHtml();
        return dd($this->cell);
        // return view('print-service::cupom', ['html' => $html]);
    }
}
