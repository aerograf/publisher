<?php declare(strict_types=1);

namespace XoopsModules\Publisher;

use XoopsUser;

/**
 *
 */
class Jsonld
{
    public $website = [];

    public static function getAuthor(XoopsUser $xoopsUser)
    {
        $author = [
            '@type'     => 'Person',
            'name'      => $xoopsUser->getVar('name') ?? $xoopsUser->getVar('uname'),
            'email'     => $xoopsUser->getVar('email') ?? '',
            'telephone' => $xoopsUser->getVar('phone') ?? '',
            "sameAs"    => [
                $xoopsUser->getVar('facebook') ?? '',//"https://www.facebook.com/your-organization-url",
                $xoopsUser->getVar('instagram') ?? '',//"https://www.instagram.com/your-organization-url/",
            ],
        ];
        return $author;
    }

    public static function getAuthoritem(XoopsUser $xoopsUser, array $xoopsConfig, string $xoops_url)
    {
        $ret    = '';
        $helper = Helper::getInstance();
        if ($helper->getConfig('generate_jsonld')) {
            $schema['@context']  = 'http://schema.org/';
            $schema['@type']     = 'Article';
            $schema['author']    = self::getAuthor($xoopsUser);
            $schema['publisher'] = self::getOrganization($xoopsConfig, $xoops_url);

            $ret = '<script type="application/ld+json">' . json_encode($schema, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES) . '</script>';
        }
        return $ret;
    }

    public static function getCategory(Category $categoryObj)
    {
        $ret    = '';
        $helper = Helper::getInstance();
        if ($helper->getConfig('generate_jsonld')) {
            global $xoopsConfig, $xoopsUser, $xoops_url;
            $schema                   = [];
            $schema['@context']       = 'http://schema.org/';
            $schema['@type']          = 'Article';
            $schema['articleSection'] = $categoryObj->getVar('name');
            $schema['publisher']      = self::getOrganization($xoopsConfig, $xoops_url);

            $ret = '<script type="application/ld+json">' . json_encode($schema, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES) . '</script>';
        }
        return $ret;
    }

    public static function getArticle(Item $itemObj, Category $categoryObj, XoopsUser $xoopsUser, Helper $helper)
    {
        $itemImage = $itemObj->getVar('image');
        if (isset($itemImage)) {
            $imageHandler = xoops_getHandler('image');
            $criteria     = new \Criteria('image_id', $itemObj->getVar('image'));
            $image        = $imageHandler->getObjects($criteria)[0];
        }
        $item = [
            "@context"        => "http://schema.org",
            '@type'           => 'Article',
            'articleSection'  => $categoryObj->getVar('name'),
            'url'             => $helper->url('item.php?itemid=') . $itemObj->getVar('itemid'),
            'headline'        => $itemObj->getVar('title'),
            'text'            => $itemObj->getVar('body'),
            'datePublished'   => $itemObj->getVar('datesub'),
            'dateModified'    => $itemObj->getVar('datesub'),
            'name'            => $xoopsUser->getVar('name') ?? $xoopsUser->getVar('uname'),
            'aggregateRating' => [
                '@type'       => 'AggregateRating',
                'ratingValue' => $itemObj->getVar('rating'),
                'ratingCount' => $itemObj->getVar('votes'),
            ],
            'commentCount'    => $itemObj->getVar('comments'),
            'image'           => [
                '@type'  => 'ImageObject',
                'url'    => XOOPS_URL . '/images/' . $image->getVar('image_name'),
                'width'  => '300',
                'height' => '60',
            ],
            'description'     => $itemObj->getVar('summary'),
        ];
        return $item;
    }

    public static function getOrganization(array $xoopsConfig, string $xoops_url)
    {
        $organization = [
            "@context" => "http://schema.org",
            "@type"    => "Organization",
            "name"     => $xoopsConfig['sitename'],
            'slogan'   => $xoopsConfig['slogan'],
            "url"      => $xoops_url,
            'logo'     => [
                '@type'  => 'ImageObject',
                'url'    => $xoops_url . '/images/logo.png',
                'width'  => '300',
                'height' => '60',
            ],
            //             "sameAs"   => [
            //                "https://www.facebook.com/your-organization-url",
            //                "https://www.instagram.com/your-organization-url/",
            //            ],
        ];
        return $organization;
    }

    public static function getWebsite($xoopsConfig, $xoops_url)
    {
        $website = [
            "@context" => "http://schema.org",
            "@type"    => "WebSite",
            "name"     => $xoopsConfig['sitename'],
            'slogan'   => $xoopsConfig['slogan'],
            "url"      => $xoops_url,
        ];
        return $website;
    }

    public static function getIndex($xoopsConfig, $xoopsUser, $xoops_url)
    {
        $ret    = '';
        $helper = Helper::getInstance();

        if ($helper->getConfig('generate_jsonld')) {
            $schema = [];

            //            $website = self::getWebsite($xoopsConfig, $xoops_url);

            $schema['@context']  = 'http://schema.org/';
            $schema['@type']     = 'Article';
            $schema['author']    = self::getAuthor($xoopsUser);
            $schema['publisher'] = self::getOrganization($xoopsConfig, $xoops_url);
            $ret                 = '<script type="application/ld+json">' . json_encode($schema, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES) . '</script>';
        }

        return $ret;
    }

    public static function getBreadcrumbs(?array $data, $settings = null)
    {
        //
        //            $breadcrumbs = [
        //                "@context"        => "http://schema.org",
        //                "@type"           => "BreadcrumbList",
        //                "itemListElement" => $listItems,
        //            ];
        //        }
        //        return $breadcrumbs;
    }

    public static function getItem(Item $itemObj, Category $categoryObj): string
    {
        $ret    = '';
        $helper = Helper::getInstance();
        if ($helper->getConfig('generate_jsonld')) {
            global $xoopsConfig, $xoopsUser, $xoops_url;
            $schema = [];
            //            $website      = self::getWebsite($xoopsConfig, $xoops_url);
            //            $category     = self::getCategory($categoryObj);
            $schema[] = [
                'article'   => self::getArticle($itemObj, $categoryObj, $xoopsUser, $helper),
                'author'    => self::getAuthor($xoopsUser),
                'publisher' => self::getOrganization($xoopsConfig, $xoops_url),
            ];

            $ret = '<script type="application/ld+json">' . json_encode($schema, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES) . '</script>';
        }

        return $ret;
    }
}
