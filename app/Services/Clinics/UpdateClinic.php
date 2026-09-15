<?php

namespace App\Services\Clinics;

class UpdateClinic
{
    public static function execute($clinic, $data)
    {
        return $clinic->update([
            'name' => array_key_exists('name', $data) ? $data['name'] : $clinic->name,
            'location_link' => array_key_exists('location_link', $data) ? $data['location_link'] : $clinic->location_link,
            'facebook' => array_key_exists('facebook', $data) ? $data['facebook'] : $clinic->facebook,
            'instgram' => array_key_exists('instgram', $data) ? $data['instgram'] : $clinic->instgram,
            'linkedin' => array_key_exists('linkedin', $data) ? $data['linkedin'] : $clinic->linkedin,
            'vezeeta' => array_key_exists('vezeeta', $data) ? $data['vezeeta'] : $clinic->vezeeta,
        ]);
    }
}
