<?php

namespace App\Enums;

enum Permission: String
{
    case CREATE_AUDIT = 'create audit';

    case READ_AUDIT = 'read audit';

    case SHOW_AUDIT = 'show audit';

    case EDIT_AUDIT = 'edit audit';



    case CREATE_IP = 'create ip';

    case READ_IP = 'read ip';

    case EDIT_IP = 'edit ip';

    case DELETE_IP = 'delete ip';
}
