<?php

namespace App\Enums;

enum Permission: String
{
    case CREATE_AUDIT = 'create audit';
    case READ_AUDIT = 'read audit';
    case SHOW_AUDIT = 'show audit';
    case EDIT_AUDIT = 'edit audit';
    case DELETE_AUDIT = 'delete audit';
}
