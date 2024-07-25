<?php

namespace App\Enums;

enum RegulationMechanismContentTypes : string
{
    case Text = 'text';
    case Image = 'image';
    case Audio = 'audio';
    case Video = 'video';
}
