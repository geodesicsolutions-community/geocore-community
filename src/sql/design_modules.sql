# Default table data for the following tables:
#  -geodesic_pages_modules_sections
#  -geodesic_pages_sections

--
-- Dumping data for table `geodesic_pages_modules_sections`
--

INSERT INTO `geodesic_pages_modules_sections` (`section_id`, `name`, `description`, `parent_section`, `display_order`) VALUES
(1, 'Browsing Modules', 'Modules that are designed to configure browsing characteristics of your site.', 0, 5),
(2, 'Featured Modules', 'Modules that are designed to configure display and browsing with respect to featured listings.', 0, 10),
(3, 'Newest Modules', 'Modules that are designed to configure display and browsing with respect to newest listings.', 0, 15),
(6, 'Miscellaneous Modules', 'Modules of various characteristics for use on your site.', 0, 30),
(7, 'Miscellaneous Display Modules', 'Modules of various characteristics related to listing display for use on your site.', 0, 35),
(8, 'Browsing Filter Modules', 'Modules designed to filter browsing results.', 1, 5),
(9, 'Category Navigation', 'Modules designed to display category navigation in different manners.', 1, 10),
(10, 'Category Tree Display Modules', 'Modules to be used for the display of the category tree structure.', 1, 15),
(11, 'Link Modules', 'Modules use to display links to various features/functions of the software.', 1, 20),
(12, 'Featured Modules - Level 1', 'Modules for displaying featured listings on your site. NOTE: If you are not using different levels of featured listings then by default all listings placed are Level 1. ', 2, 5),
(13, 'Featured Modules - Level 2', 'Modules for displaying featured listings on your site that are of a level status 2 only. NOTE: If you are not using different levels of featured listings then by default all listings placed are Level 1. ', 2, 10),
(14, 'Featured Modules - Level 3', 'Modules for displaying featured listings on your site that are of a level status 3 only. NOTE: If you are not using different levels of featured listings then by default all listings placed are Level 1. ', 2, 15),
(15, 'Featured Modules - Level 4', 'Modules for displaying featured listings on your site that are of a level status 4 only. NOTE: If you are not using different levels of featured listings then by default all listings placed are Level 1. ', 2, 20),
(16, 'Featured Modules - Level 5', 'Modules for displaying featured listings on your site that are of a level status 2 only. NOTE: If you are not using different levels of featured listings then by default all listings placed are Level 1. ', 2, 25);

--
-- Dumping data for table `geodesic_pages_sections`
--

INSERT INTO `geodesic_pages_sections` (`section_id`, `name`, `description`, `parent_section`, `display_order`, `applies_to`) VALUES
(1, 'Browsing Listings', 'This section includes the home page, categories displayed while browsing, ad display, notify friend and send seller a question sections of the site', 0, 1, 0),
(2, 'Listing Process', 'This section includes all the pages displayed when a registered user places a listing.', 0, 2, 0),
(3, 'Registration', 'This section includes all of the pages that are displayed when a new user tries to register', 0, 3, 0),
(4, 'User Management', 'This section includes all of the pages displayed when the user makes changes to their account.  Pages included are current ads, expired ads, ad filters, personal info, favorites and messaging.', 0, 4, 0),
(5, 'Login and Languages', 'This section includes the login page and the language selection pages', 0, 5, 0),
(7, 'User Communications Sub-Section', 'This section contains the pages of the users communication view, replying and configuration section within the Users Management Section.', 4, 0, 0),
(8, 'Users Filter Sub-Section', 'This sub-section of Users Management allows the user to create and delete ad filters to search newly placed classified ads.', 4, 0, 0),
(9, 'Users Expired Listings Sub-Section', 'This section allows the user to list and view their expired ads.', 4, 0, 0),
(10, 'Users Current Listings', 'This section allows the user to view and edit their current live classified ads.', 4, 0, 0),
(11, 'Users Information Management', 'This section allows the user to view and edit their current information saved on the site.', 4, 0, 0),
(12, 'Extra Pages', 'This section contains the extra pages you can use for site supporting documents but still take advantage of module placement', 0, 6, 0),
(13, 'Client Side Auction Feedback', 'This sub-section allows the user to view feedback about themselves and leave feedback for others.', 4, 0, 2),
(14, 'Bidding', 'This section contains all of the pages allowing the user to place bids on auctions.', 0, 6, 2),
(15, 'General Template Text', '', 0, 7, 0);

