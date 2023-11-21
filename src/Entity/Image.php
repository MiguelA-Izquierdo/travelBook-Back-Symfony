<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


class Image
{
   #[ORM\Column(type: 'string')]
    private string $brochureFilename;

    public function getBrochureFilename(): string
    {
        return $this->brochureFilename;
    }

    public function setBrochureFilename(string $brochureFilename): self
    {
        $this->brochureFilename = $brochureFilename;

        return $this;
    }
}
