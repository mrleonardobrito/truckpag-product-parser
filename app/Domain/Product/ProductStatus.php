<?php

namespace App\Domain\Product;

enum ProductStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case TRASH = 'trash';
}
