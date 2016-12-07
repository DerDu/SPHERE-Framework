<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpWord\File;
use MOC\V\Core\AutoLoader\AutoLoader;

/**
 * Class PhpWord
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository
 */
class PhpWord extends File
{

    /**
     * PhpWord constructor.
     */
    public function __construct()
    {

        AutoLoader::getNamespaceAutoLoader('PhpOffice\PhpWord',
            __DIR__.'/../../../Vendor/PhpWord/0.12.1/src/PhpWord',
            'PhpOffice\PhpWord'
        );
    }
}
