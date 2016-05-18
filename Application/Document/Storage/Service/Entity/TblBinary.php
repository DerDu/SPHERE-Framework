<?php
namespace SPHERE\Application\Document\Storage\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBinary")
 * @Cache(usage="READ_ONLY")
 */
class TblBinary extends Element
{

    const ATTR_HASH = 'Hash';
    /**
     * @Column(type="blob")
     */
    protected $BinaryBlob;
    /**
     * @Column(type="string")
     */
    protected $Hash;

    /**
     * @return string|resource
     */
    public function getBinaryBlob()
    {

        if (is_resource($this->BinaryBlob)) {
            return stream_get_contents($this->BinaryBlob);
        } else {
            return $this->BinaryBlob;
        }
    }

    /**
     * @param string $BinaryBlob
     */
    public function setBinaryBlob($BinaryBlob)
    {

        $this->BinaryBlob = $BinaryBlob;
        $this->Hash = hash_hmac('sha256', $this->getBinaryBlob(),
            'HbGQLxc378gOWqiA9YR0QMV36boVRmZ5wD69pILKlChtAO1c1kOvuXzGM5zKVIn' // WPA-2
        );
    }

    /**
     * @return string
     */
    public function getHash()
    {

        return $this->Hash;
    }
}
