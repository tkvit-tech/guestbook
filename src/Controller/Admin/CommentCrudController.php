<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;


class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
    public function configureFields(string $pageName): iterable
    {
        /*return [
            'id',
            'author',
            'text',
            'email',
            'createdAt',
            'conference',
            'photoFilename'
        ];*/
        return [
            //IdField::new('id'),
            TextField::new('author'),
            TextEditorField::new('text'),
            AssociationField::new('conference'),
            TextField::new('email'),
            DateTimeField::new('createdAt'),
            //TextField::new('photoFilename')
            TextField::new('state'),
            ImageField::new('photoFilename')
                ->setBasePath('/guestbook')
                ->setLabel('Photo')
                ->onlyOnIndex()

        ];
    }
}
