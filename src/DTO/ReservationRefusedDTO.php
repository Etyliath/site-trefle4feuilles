<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ReservationRefusedDTO
{
    #[Assert\NotBlank]
    #[Assert\Length( min: 3, max: 255 )]
    public string $message = '';

}