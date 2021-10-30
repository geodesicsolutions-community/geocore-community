<?php

//addons/adplotter/util.php

# Adplotter Link addon

class addon_adplotter_util extends addon_adplotter_info
{

    public function getAdPlotterCategories($forParent = '')
    {
        //for now, return a hardcoded array
        //in the future, might be worth pulling this list dynamically from AP's servers.
        //as long as this function still returns the data in array format, nothing should break

        $data = array(
            "AirCraft"
                => array(
                    "AirCraft -> Parts & Accessories",
                ),
            "Antiques"
                => array(
                    "Antiques ->  Ethnographic",
                    "Antiques -> Antiquities (Classical, Amer.)",
                    "Antiques -> Architectural & Garden",
                    "Antiques -> Asian Antiques",
                    "Antiques -> Books, Manuscripts",
                    "Antiques -> Decorative Arts",
                    "Antiques -> Furniture",
                    "Antiques -> Maps, Atlases,Globes",
                    "Antiques -> Maritime",
                    "Antiques -> Musical Instruments",
                ),
            "Appliances"
                => array(
                ),
            "Art"
                => array(
                    "Art -> Ceramic Art & Pottery",
                    "Art -> Digital Art",
                    "Art -> Drawings",
                    "Art -> Folk Art",
                    "Art -> Mixed Media",
                    "Art -> Paintings",
                    "Art -> Photographic Images",
                    "Art -> Posters",
                    "Art -> Prints",
                    "Art -> Sculpture, Carvings",
                    "Art -> Stone Art",
                ),
            "Baby Stuff"
                => array(
                    "Baby Stuff -> Baby Gear",
                    "Baby Stuff -> Baby Safety & Health",
                    "Baby Stuff -> Bathing & Grooming",
                    "Baby Stuff -> Car Safety Seats",
                    "Baby Stuff -> Diapering",
                    "Baby Stuff -> Feeding",
                    "Baby Stuff -> Keepsakes & Baby Announcements",
                    "Baby Stuff -> Nursery Bedding",
                    "Baby Stuff -> Nursery Decor",
                    "Baby Stuff -> Nursery Furnitury",
                    "Baby Stuff -> Strollers",
                ),
            "Bicycles"
                => array(
                ),
            "Boats & Watercraft"
                => array(
                    "Boats & Watercraft -> Fishing Boats",
                    "Boats & Watercraft -> Others",
                    "Boats & Watercraft -> Personal Watercraft",
                    "Boats & Watercraft -> Power Boats & Motor Boats",
                    "Boats & Watercraft -> Sailboats",
                ),
            "Books"
                => array(
                    "Books -> Accessories",
                    "Books -> Antiwuarian & Collectible",
                    "Books -> Audio Books",
                    "Books -> Catalogs",
                    "Books -> Childrens Books",
                    "Books -> Electronic Books",
                    "Books -> Fiction Books",
                    "Books -> Magazine Back Issues",
                    "Books -> Magazine Subscriptions",
                    "Books -> Nonfiction Books",
                    "Books -> Service Manuals",
                    "Books -> Textbooks,Education",
                    "Books -> Wholesale,Bulk Lots",
                ),
            "Business & Industrial"
                => array(
                    "Business & Industrial -> Agriculture & Forestry",
                    "Business & Industrial -> Construction",
                    "Business & Industrial -> Food Service & Retail",
                    "Business & Industrial -> For Sale",
                    "Business & Industrial -> Healthcare,Lab & Life Science",
                    "Business & Industrial -> Industrial Electrical & Test",
                    "Business & Industrial -> Industrial Supply,MRO",
                    "Business & Industrial -> Manufacturing & Metalworking",
                    "Business & Industrial -> Office,Printing & Shipping",
                    "Business & Industrial -> Other Industries,",
                ),
            "Business Opportunities"
                => array(
                ),
            "Cameras & photo"
                => array(
                    "Cameras & photo -> Bags,Cases & Straps",
                    "Cameras & photo -> Binoculars & Telescopes",
                    "Cameras & photo -> Camcoder Accessories",
                    "Cameras & photo -> Camcorders",
                    "Cameras & photo -> Digital Camera Accessories",
                    "Cameras & photo -> Digital cameras",
                    "Cameras & photo -> Film",
                    "Cameras & photo -> Film Camera Accessories",
                    "Cameras & photo -> Film Cameras",
                    "Cameras & photo -> Film Processing & Darkroom",
                    "Cameras & photo -> Flashes & Accessories",
                    "Cameras & photo -> Lenses & Filters",
                    "Cameras & photo -> Lighting & Studio Equipment",
                    "Cameras & photo -> Manuals,Guides & Books",
                    "Cameras & photo -> Photo Albums & Archive Items",
                    "Cameras & photo -> Printers,Scanners & Supplies",
                    "Cameras & photo -> Professional Video Equipment",
                    "Cameras & photo -> Projection Equipment",
                    "Cameras & photo -> Stock Photography & Footage",
                    "Cameras & photo -> Tripods,Monopods",
                    "Cameras & photo -> Vintage",
                    "Cameras & photo -> Wholesale Lots",
                ),
            "Cars & Vehicles"
                => array(
                    "Cars & Vehicles -> Buses",
                    "Cars & Vehicles -> Cars",
                    "Cars & Vehicles -> Classic Cars",
                    "Cars & Vehicles -> Others",
                    "Cars & Vehicles -> Parts & Accessories",
                    "Cars & Vehicles -> SUVs",
                    "Cars & Vehicles -> Trucks",
                ),
            "Cell Phones"
                => array(
                    "Cell Phones -> Accessories,Parts",
                    "Cell Phones -> Phones Only",
                    "Cell Phones -> Phones with New Plan Purchase",
                    "Cell Phones -> Prepaid Phones & Cards",
                    "Cell Phones -> Wholesale & Large Lots",
                ),
            "Clothing ,Shoes & Accessories"
                => array(
                    "Clothing ,Shoes & Accessories -> Boys",
                    "Clothing ,Shoes & Accessories -> Girls",
                    "Clothing ,Shoes & Accessories -> Infants & Toddlers",
                    "Clothing ,Shoes & Accessories -> Mens Accessories",
                    "Clothing ,Shoes & Accessories -> Mens Clothing",
                    "Clothing ,Shoes & Accessories -> Mens Shoes",
                    "Clothing ,Shoes & Accessories -> Uniforms",
                    "Clothing ,Shoes & Accessories -> Vintage",
                    "Clothing ,Shoes & Accessories -> Wedding Apparel",
                    "Clothing ,Shoes & Accessories -> WholesaleLarge & Small Lots",
                    "Clothing ,Shoes & Accessories -> Womens Accessories,Handbags",
                    "Clothing ,Shoes & Accessories -> Womens Clothing",
                    "Clothing ,Shoes & Accessories -> Womens Shoes",
                ),
            "Coins"
                => array(
                    "Coins -> Bullion",
                    "Coins -> Coins: US",
                    "Coins -> Coins:Ancient",
                    "Coins -> Coins:World",
                    "Coins -> Exonumia",
                    "Coins -> Other",
                    "Coins -> Paper Money:US",
                    "Coins -> Paper Money:World",
                    "Coins -> Publications & Supplies",
                    "Coins -> Scripophily",
                ),
            "Collectibles"
                => array(
                    "Collectibles -> Advertising",
                    "Collectibles -> Animals",
                    "Collectibles -> Animation Art,Characters",
                    "Collectibles -> Arcade,Jukeboxes & Pinball",
                    "Collectibles -> Autographs",
                    "Collectibles -> Banks,Registers & Vending",
                    "Collectibles -> Barware",
                    "Collectibles -> Bottles & Insulators",
                    "Collectibles -> Breweriana,Beer",
                    "Collectibles -> Casino",
                    "Collectibles -> Clocks",
                    "Collectibles -> Collectible Rugs",
                    "Collectibles -> Comics",
                    "Collectibles -> Cultures,Ethnicities",
                    "Collectibles -> Decorative Collectibles",
                    "Collectibles -> Disneyana",
                    "Collectibles -> Fantasy,Mythical & Magic",
                    "Collectibles -> Furniture,Appliances & Fans",
                    "Collectibles -> Historical Memorabillia",
                    "Collectibles -> Holiday,Seasonal",
                    "Collectibles -> Housewares & Kitchenware",
                    "Collectibles -> Knives, Swords & Blades",
                    "Collectibles -> Knives,Swords & Blades",
                    "Collectibles -> Lamps,Lighting",
                    "Collectibles -> Lines,Fabric & Textiles",
                    "Collectibles -> Metalware",
                    "Collectibles -> Militaria",
                    "Collectibles -> Pens & Writing Instruments",
                    "Collectibles -> Pez,Keychains,Promo Glasses",
                    "Collectibles -> Photographic Images",
                    "Collectibles -> Pinbacks,Nodders,Lunchboxes",
                    "Collectibles -> Postcards & Paper",
                    "Collectibles -> Radio,Phonograph,TV,Phone",
                    "Collectibles -> Religions,Spiritulaity",
                    "Collectibles -> Rocks,Fossils.Minerals",
                    "Collectibles -> Science Fiction",
                    "Collectibles -> Science,Medical",
                    "Collectibles -> Sports Cards",
                    "Collectibles -> Tobacciana",
                    "Collectibles -> Tools,Hardware & Locks",
                    "Collectibles -> Trading Cards",
                    "Collectibles -> Transportation",
                    "Collectibles -> Vanity,Perfume & Shaving",
                    "Collectibles -> Vintage Sewing",
                    "Collectibles -> Wholesale Lots",
                ),
            "Computer and Networking"
                => array(
                    "Computer and Networking -> Apple,Macintosh Computers",
                    "Computer and Networking -> Desktop PCs",
                    "Computer and Networking -> Hardware",
                    "Computer and Networking -> Laptops,Notebooks",
                    "Computer and Networking -> Monitors & Projectors",
                    "Computer and Networking -> Software",
                ),
            "ConsumerElectronics"
                => array(
                    "ConsumerElectronics -> Car Electronics",
                    "ConsumerElectronics -> Clocks",
                    "ConsumerElectronics -> Digital Video Recorders,PVR",
                    "ConsumerElectronics -> DVD Players & Recorders",
                    "ConsumerElectronics -> Gadgets & Other Electronics",
                    "ConsumerElectronics -> GPS Devices",
                    "ConsumerElectronics -> Home Audio",
                    "ConsumerElectronics -> Home Theater in a Box",
                    "ConsumerElectronics -> Home Theator Projectors",
                    "ConsumerElectronics -> MP3 Players & Accessories",
                    "ConsumerElectronics -> PDAs/Handheld PCs",
                    "ConsumerElectronics -> Portable Audio",
                    "ConsumerElectronics -> Radios;CB,Ham & Shortwave",
                    "ConsumerElectronics -> Satellite Radio",
                    "ConsumerElectronics -> Satellite,Cable TV",
                    "ConsumerElectronics -> Telephone & Pagers",
                    "ConsumerElectronics -> Televisions",
                    "ConsumerElectronics -> VCRs",
                    "ConsumerElectronics -> Vintage Electronics",
                    "ConsumerElectronics -> Wholesale Lots",
                ),
            "Crafts"
                => array(
                    "Crafts -> Basketry",
                    "Crafts -> Bead Art",
                    "Crafts -> Candle & Soap making",
                    "Crafts -> Ceramics,Pottery",
                    "Crafts -> Crocheting",
                    "Crafts -> Cross stitch",
                    "Crafts -> Decorative,Tole painting",
                    "Crafts -> Drawing",
                    "Crafts -> Embroidery",
                    "Crafts -> Fabric",
                    "Crafts -> Fabric Embellishments",
                    "Crafts -> Floral Crafts",
                    "Crafts -> Framing & Matting",
                    "Crafts -> General Art & Craft Supplies",
                    "Crafts -> Glass Art Crafts",
                    "Crafts -> Handcrafted Items",
                    "Crafts -> Kids Crafts",
                    "Crafts -> Knitting",
                    "Crafts -> Lacemaking,Tatting",
                    "Crafts -> Latch Rug Hooking",
                    "Crafts -> Leathercraft",
                    "Crafts -> Macrame",
                    "Crafts -> Metal Working",
                    "Crafts -> Mosaic",
                    "Crafts -> Needle Point",
                    "Crafts -> Painting",
                ),
            "Dolls & Bears"
                => array(
                ),
            "DVDs and Movies"
                => array(
                ),
            "Entertainment Memorabilia"
                => array(
                ),
            "Everything Else"
                => array(
                ),
            "Furniture"
                => array(
                    "Furniture -> Office Furniture",
                ),
            "Gift Certificates"
                => array(
                ),
            "Health & Beauty"
                => array(
                    "Health & Beauty -> Bath & Body",
                    "Health & Beauty -> Coupons",
                    "Health & Beauty -> Dietary Suppliments,Nutrition",
                    "Health & Beauty -> Fragrances",
                    "Health & Beauty -> Hair care",
                    "Health & Beauty -> Hair Removal",
                    "Health & Beauty -> Health Care",
                    "Health & Beauty -> Makeup",
                    "Health & Beauty -> Massage",
                    "Health & Beauty -> medical,Special Needs",
                    "Health & Beauty -> Nail",
                    "Health & Beauty -> Natural Therapies",
                    "Health & Beauty -> Oral Care",
                    "Health & Beauty -> Other health & Beauty Items",
                    "Health & Beauty -> Over the Counter Medicine",
                    "Health & Beauty -> Skin Care",
                    "Health & Beauty -> Tanning Beds,Lamps",
                    "Health & Beauty -> Tattoos,Body Art",
                    "Health & Beauty -> Vision Care",
                    "Health & Beauty -> Weight management",
                    "Health & Beauty -> Wholesale Lots",
                ),
            "Home & Garden"
                => array(
                    "Home & Garden -> Basement Finishing Kits",
                    "Home & Garden -> Bath",
                    "Home & Garden -> Bedding",
                    "Home & Garden -> Building & Hardware",
                    "Home & Garden -> Dining & Bar",
                    "Home & Garden -> Electrical & Solar",
                    "Home & Garden -> Food & Wine",
                    "Home & Garden -> Furniture",
                    "Home & Garden -> Gardening & Plants",
                    "Home & Garden -> Heating ,Cooling & Air",
                    "Home & Garden -> Home Decor",
                    "Home & Garden -> Home security",
                    "Home & Garden -> Kitchen",
                    "Home & Garden -> Lamps,Lighting ,Ceiling Fans",
                    "Home & Garden -> Major Appliances",
                    "Home & Garden -> outdoor Power Equipment",
                    "Home & Garden -> Pattio & Grilling",
                    "Home & Garden -> Pet Supplies",
                    "Home & Garden -> Plumbing & Fixtures",
                    "Home & Garden -> Pools & Spas",
                    "Home & Garden -> Rugs & Carpets",
                    "Home & Garden -> Tools",
                    "Home & Garden -> Vacuum Cleaners & Housekeeping",
                    "Home & Garden -> Wholesale Lots",
                    "Home & Garden -> Window Treatments",
                ),
            "Jewelry & Watches"
                => array(
                    "Jewelry & Watches -> Body Jewelry",
                    "Jewelry & Watches -> Bracelets",
                    "Jewelry & Watches -> Charms & charms bracelets",
                    "Jewelry & Watches -> Childrens Jewelry",
                    "Jewelry & Watches -> Designer Brands",
                    "Jewelry & Watches -> Earrings",
                    "Jewelry & Watches -> Ethnic,Tribal Jewelry",
                    "Jewelry & Watches -> Hair Jewelry",
                    "Jewelry & Watches -> Handcrafted ,Artisan Jewelry",
                    "Jewelry & Watches -> Jewelry Boxes & Supplies",
                    "Jewelry & Watches -> Loose Beads",
                    "Jewelry & Watches -> Loose Diamonds & Gemstones",
                    "Jewelry & Watches -> Mens Jewelry",
                    "Jewelry & Watches -> Necklaces & Pendants",
                    "Jewelry & Watches -> Pins, Brooches",
                    "Jewelry & Watches -> Rings",
                    "Jewelry & Watches -> Sets",
                    "Jewelry & Watches -> Vintage, Antique",
                    "Jewelry & Watches -> Watches",
                ),
            "Jobs"
                => array(
                ),
            "Lost and Found"
                => array(
                ),
            "MLM Products"
                => array(
                ),
            "Motorcycles & ATVs"
                => array(
                    "Motorcycles & ATVs -> ATVs",
                    "Motorcycles & ATVs -> BMW",
                    "Motorcycles & ATVs -> Harley Davidson",
                    "Motorcycles & ATVs -> Honda",
                    "Motorcycles & ATVs -> Kawasaki",
                    "Motorcycles & ATVs -> KTM",
                    "Motorcycles & ATVs -> Suzuki",
                    "Motorcycles & ATVs -> Yamaha",
                ),
            "Music"
                => array(
                    "Music -> Accessories",
                    "Music -> Cassettes",
                    "Music -> CDs",
                    "Music -> Digital Music Downloads",
                    "Music -> DVD Audio",
                    "Music -> Other Formats",
                    "Music -> Records",
                    "Music -> Super Audio CDs",
                    "Music -> Wholeslae Lots",
                ),
            "Musical Instruments"
                => array(
                    "Musical Instruments -> Brass",
                    "Musical Instruments -> DJ Gear & Lighting",
                    "Musical Instruments -> Electronic",
                    "Musical Instruments -> Equipment",
                    "Musical Instruments -> Guitar",
                    "Musical Instruments -> Harmonica",
                    "Musical Instruments -> Instruction Books,CDs Videos",
                    "Musical Instruments -> Keyboard,Piano",
                    "Musical Instruments -> Other Instruments",
                    "Musical Instruments -> Percussion",
                    "Musical Instruments -> Pro Audio",
                    "Musical Instruments -> Sheet Music,Song Books",
                    "Musical Instruments -> String",
                    "Musical Instruments -> Wholesale Lots",
                    "Musical Instruments -> Wood wind",
                ),
            "Pet Supplies"
                => array(
                    "Pet Supplies -> Aquarium & Fish",
                    "Pet Supplies -> Bird Supplies",
                    "Pet Supplies -> Cat Supplies",
                    "Pet Supplies -> Dog Supplies",
                    "Pet Supplies -> Other",
                ),
            "Real Estate"
                => array(
                    "Real Estate -> Commercial",
                    "Real Estate -> Land HomePage",
                    "Real Estate -> Manufactured Homes",
                    "Real Estate -> Other Real Estate",
                    "Real Estate -> Residential Homes",
                    "Real Estate -> Timeshares Homepage",

                ),
            "Remodeling Services"
                => array(
                ),
            "RVs & Campers"
                => array(
                    "RVs & Campers -> 5th Wheeler",
                    "RVs & Campers -> Motorhomes",
                    "RVs & Campers -> Other trailers",
                    "RVs & Campers -> Travel Trailer",
                ),
            "Speciality Services"
                => array(
                    "Speciality Services -> Advice & Instruction",
                    "Speciality Services -> Artistic Services",
                    "Speciality Services -> Catering Service",
                    "Speciality Services -> Custom Clothing & Jewelry",
                    "Speciality Services -> Graphic & Logo Design",
                    "Speciality Services -> Media Editing & Duplication",
                    "Speciality Services -> Other Sevices",
                    "Speciality Services -> Printing & Personalization",
                    "Speciality Services -> Restoration & Repair",
                    "Speciality Services -> Web & Computer Services",
                ),
            "Sporting Goods"
                => array(
                    "Sporting Goods -> Baseball",
                    "Sporting Goods -> Camping & Equipment",
                    "Sporting Goods -> Cycling",
                    "Sporting Goods -> Equestrian",
                    "Sporting Goods -> Exercise & Fitness",
                    "Sporting Goods -> Fishing",
                    "Sporting Goods -> Golf",
                    "Sporting Goods -> Hunting",
                ),
            "Sports Mem,Cards & Fan Shop"
                => array(
                    "Sports Mem,Cards & Fan shop -> Art",
                    "Sports Mem,Cards & Fan shop -> Authinticated Pre-certified",
                    "Sports Mem,Cards & Fan shop -> Autographs-Orginal",
                    "Sports Mem,Cards & Fan shop -> Autographs-Reprints",
                    "Sports Mem,Cards & Fan shop -> Cards",
                    "Sports Mem,Cards & Fan shop -> Fan Apparel & Souvenirs",
                    "Sports Mem,Cards & Fan shop -> Game Used Memorbilia",
                    "Sports Mem,Cards & Fan shop -> Vintage Sports Memorbilia",
                    "Sports Mem,Cards & Fan shop -> Wholesale Lots",
                ),
            "Stamps"
                => array(
                    "Stamps -> Africa",
                    "Stamps -> Asia",
                    "Stamps -> Australia",
                    "Stamps -> Br.Comm.Other",
                    "Stamps -> Canada",
                    "Stamps -> Europe",
                    "Stamps -> Latin America",
                    "Stamps -> Middle East",
                    "Stamps -> Pubblications & Supplies",
                    "Stamps -> Topical & Specialty",
                    "Stamps -> UK(Great Britian)",
                    "Stamps -> United States",
                    "Stamps -> World wide",
                ),
            "Tickets"
                => array(
                    "Tickets -> Concert Tickets",
                    "Tickets -> Everything Else",
                    "Tickets -> MLB Tickets",
                    "Tickets -> Nascar Tickets",
                    "Tickets -> NBA Tickets",
                    "Tickets -> NBA Tickets",
                    "Tickets -> NFL Tickets",
                ),
            "Toys & Hobbies"
                => array(
                    "Toys & Hobbies -> Action Figures",
                    "Toys & Hobbies -> Beanbag Plush,Beanie Babies",
                    "Toys & Hobbies -> Building Toys",
                    "Toys & Hobbies -> Classic Toys",
                    "Toys & Hobbies -> Diecast,Toy vehicles",
                    "Toys & Hobbies -> Educational",
                    "Toys & Hobbies -> Electrnonic,Battery Wind-up",
                    "Toys & Hobbies -> Fast Food,Cereal Premiums",
                    "Toys & Hobbies -> Games",
                    "Toys & Hobbies -> Model RR ,Trains",
                    "Toys & Hobbies -> Models,Kits",
                    "Toys & Hobbies -> Outdoor Toys,Structures",
                    "Toys & Hobbies -> Pretend Play,PreSchool",
                    "Toys & Hobbies -> Puzzles",
                    "Toys & Hobbies -> Radio Control",
                    "Toys & Hobbies -> Robots,Monsters,Space Toys",
                    "Toys & Hobbies -> Slot Cars",
                    "Toys & Hobbies -> Stuffed Animals",
                ),
            "Travel"
                => array(
                    "Travel -> Airline",
                    "Travel -> Cruises",
                    "Travel -> Lodging",
                    "Travel -> Luggage",
                ),
            "Video Games"
                => array(
                ),
            "Lost/Found Pet"
                => array(
                ),
        );
        if ($forParent) {
            //looking for just a specific parent and its subcategories
            return $data[$forParent] ? $data[$forParent] : false;
        }
        return $data;
    }

    public function getParentKeys()
    {
        //give each top-level adplotter category a numerical key, to make things a bit easier to work with on our end

        $data = array(
            "AirCraft" => 0,
            "Antiques" => 1,
            "Appliances" => 2,
            "Art" => 3,
            "Baby Stuff" => 4,
            "Bicycles" => 5,
            "Boats & Watercraft" => 6,
            "Books" => 7,
            "Business & Industrial" => 8,
            "Business Opportunities" => 9,
            "Cameras & photo" => 10,
            "Cars & Vehicles" => 11,
            "Cell Phones" => 12,
            "Clothing ,Shoes & Accessories" => 13,
            "Coins" => 14,
            "Collectibles" => 15,
            "Computer and Networking" => 16,
            "ConsumerElectronics" => 17,
            "Crafts" => 18,
            "Dolls & Bears" => 19,
            "DVDs and Movies" => 20,
            "Entertainment Memorabilia" => 21,
            "Everything Else" => 22,
            "Furniture" => 23,
            "Gift Certificates" => 24,
            "Health & Beauty" => 25,
            "Home & Garden" => 26,
            "Jewelry & Watches" => 27,
            "MLM Products" => 28,
            "Motorcycles & ATVs" => 29,
            "Music" => 30,
            "Musical Instruments" => 31,
            "Pet Supplies" => 32,
            "Real Estate" => 33,
            "Remodeling Services" => 34,
            "RVs & Campers" => 35,
            "Speciality Services" => 36,
            "Sporting Goods" => 37,
            "Sports Mem,Cards & Fan Shop" => 38,
            "Stamps" => 39,
            "Tickets" => 40,
            "Toys & Hobbies" => 41,
            "Travel" => 42,
            "Video Games" => 43,
            "Lost/Found Pet" => 44,
            "Jobs" => 45,
            "Lost and Found" => 46
        );
        return $data;
    }

    public function core_sell_success_email_content($vars)
    {
        //add affiliate link to sell success emails
        $reg = geoAddon::getRegistry($this->name);
        $affCode = $reg->affiliate_code;
        if (!$affCode) {
            //no affiliate code entered. nothing to do here
            return $vars;
        }

        $listing = $vars['listing'];
        if ($listing->item_type != 1) {
            //only modify emails for classified listings
            return $vars;
        }

        //username is the email
        $username = geoString::fromDB($listing->email);

        //see if we have previously registered this local user (NOTE these are stored by email, to handle anonymous listings)
        $db = DataAccess::getInstance();
        $completed = $db->GetOne("SELECT `email` FROM `geodesic_addon_adplotter_affiliate_registrations` WHERE `email` = ?", array($username));
        if (strlen($completed)) {
            //already done for this address
            return $vars;
        }

        $user = geoUser::getUser($listing->seller);
        $firstName = ($user && $user->firstname) ? $user->firstname : '';
        $lastName = ($user && $user->lastname) ? $user->lastname : '';


        //create a random password
        $password = substr(md5(time() . mt_rand(10000)), 7, 12);
        //make the API call to register a new AdPlotter user
        $api_url = "http://api.adplotter.com/ProcessAPIRequest.ashx";
        $params = array(
            'Action' => 'SaveUser',
            'AffiliateSponsorID' => $affCode,
                'Email' => $username,
                'Username' => $username, //must be present, and the same as "Email"
                'Password' => $password,
                'IP' => getenv('REMOTE_ADDR'),
                'Referer' => str_replace($db->get_site_setting('classifieds_file_name'), '', $db->get_site_setting('classifieds_url')),
                'FirstName' => $firstName,
                'LastName' => $lastName,
                //these values are required to be present as at least empty strings by the API, even though we don't really use them right now
                'Address' => '',
                'City' => '',
                'State' => '',
                'Zip' => '',
                'Phone' => '',
                'Country' => '',
        );
        $apiResult = json_decode(geoPC::urlPostContents($api_url, $params), true);
        if (!$apiResult || $apiResult['UserID'] <= 0) {
            //api call failed, or perhaps this user already exists in AdPlotter
            return $vars;
        }

        //completed, so mark email down as done
        $db->Execute('INSERT INTO `geodesic_addon_adplotter_affiliate_registrations` (`email`) VALUES (?)', array($username));

        $msgs = geoAddon::getText('geo_addons', $this->name);

        $tpl = new geoTemplate('addon', $this->name);
        $tpl->assign('msgs', $msgs);
        $tpl->assign('username', $username);
        $tpl->assign('password', $password);
        $add = $tpl->fetch('admin/email_aff.tpl');
        $vars['content'] .= $add;

        return $vars;
    }
}
