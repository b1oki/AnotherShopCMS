<?php
// ������������ ��������� URL �������.
$routes = array(
    // ������� �������� ����� (/)
    array(
        // ������� � ������� Perl-������������ ���������� ���������
        'pattern' => '~^(/main)?/$~',
        // ��� ������ �����������
        'class' => 'Index',
        // ��� ������ ������ �����������
        'method' => 'main'
    ),
    // �������� ���������� � �������� (/company)
    array(
        'pattern' => '~^/company/$~',
        'class' => 'Index',
        'method' => 'company',
    ),
    // �������� ��������� �������� (/contacts)
    array(
        'pattern' => '~^/contacts/$~',
        'class' => 'Index',
        'method' => 'contacts',
    ),
    // �������� � ���������� ��������� (/news)
    array(
        'pattern' => '~^/news/$~',
        'class' => 'News',
        'method' => 'newest',
    ),
    // �������� � ����� �������� (/news/12345)
    array(
        'pattern' => '~^/news/(?P<article_id>[0-9]+)/$~',
        'class' => 'News',
        'method' => 'article',
    ),
    // ������� � ���������� (/product/box/red/)
    array(
        'pattern' => '~^/product/(?P<category_path>([a-zA-Z_/\-]+/)*)$~',
        'class' => 'Catalog',
        'method' => 'main',
    ),
    // ����� (/product/box/red/item/3)
    array(

        'pattern' => '~^/product/(?P<category_path>([a-zA-Z_/\-]+/)*)(?P<item_id>[0-9]+)/$~',
        'class' => 'Catalog',
        'method' => 'item',
    ),
);