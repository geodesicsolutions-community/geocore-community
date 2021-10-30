<?php

/**
 * Holds class geoTables, which holds database table names, used throughout the code.
 *
 * @package System
 */


/**
 * Used to hold database table names, used throughout the code.
 *
 * @package System
 */
class geoTables
{
    //tables within the database
    // (Where the magic happens)
    const master = "`geodesic_master`";
    const auctions_table = "`geodesic_classifieds`";
    const classifieds_table = "`geodesic_classifieds`";
    const classifieds_expired_table = "`geodesic_classifieds_expired`";
    const classified_sell_questions_table = "`geodesic_classifieds_sell_questions`";
    const classified_extra_table = "`geodesic_classifieds_ads_extra`";
    const categories_table = "`geodesic_categories`";
    const categories_languages_table = "`geodesic_categories_languages`";
    const category_exclusion_list = "`geodesic_category_exclude_list_types`";
    const categories_exclude_per_price_plan_table = "`geodesic_categories_excluded_per_price_plan`";
    const listing_categories = "`geodesic_listing_categories`";
    const logins_table = "`geodesic_logins`";
    const configuration_table = "`geodesic_configuration`";
    const sell_choices_table = "`geodesic_classifieds_sell_question_choices`";
    const sell_choices_types_table = "`geodesic_classifieds_sell_question_types`";
    const questions_table = "`geodesic_classifieds_sell_questions`";
    const questions_languages = "`geodesic_classifieds_sell_questions_languages`";
    const states_table = "`geodesic_states`";
    const state_languages = "`geodesic_state_languages`";
    const countries_table = "`geodesic_countries`";
    const country_languages = "`geodesic_country_languages`";
    const text_message_table = "`geodesic_text_messages`";
    const text_languages_table = "`geodesic_text_languages`";
    const text_languages_messages_table = "`geodesic_text_languages_messages`";
    const text_page_table = "`geodesic_text_pages`";
    const text_subpages_table = "`geodesic_text_subpages`";
    const confirm_table = "`geodesic_confirm`";
    const confirm_email_table = "`geodesic_confirm_email`";
    const userdata_table = "`geodesic_userdata`";
    const badwords_table = "`geodesic_text_badwords`";
    const ad_configuration_table = "`geodesic_classifieds_ad_configuration`";
    const userdata_history_table = "`geodesic_userdata_history`";
    const html_allowed_table = "`geodesic_html_allowed`";
    const ad_filter_table = "`geodesic_ad_filter`";
    const ad_filter_categories_table = "`geodesic_ad_filter_categories`";
    const user_communications_table = "`geodesic_user_communications`";
    const site_configuration_table = "`geodesic_classifieds_configuration`";
    const choices_table = "`geodesic_choices`";
    const images_urls_table = "`geodesic_classifieds_images_urls`";
    const favorites_table = "`geodesic_favorites`";
    const file_types_table = "`geodesic_file_types`";
    const groups_table = "`geodesic_groups`";
    const group_questions_table = "`geodesic_classifieds_group_questions`";
    const price_plans_table = "`geodesic_classifieds_price_plans`";
    const price_plans_categories_table = "`geodesic_classifieds_price_plans_categories`";
    const price_plans_increments_table = "`geodesic_classifieds_price_increments`";
    const user_groups_price_plans_table = "`geodesic_user_groups_price_plans`";
    const expirations_table = "`geodesic_classifieds_expirations`";
    const user_tokens = "`geodesic_user_tokens`";
    const credit_choices = "`geodesic_classifieds_credit_choices`";
    const user_subscriptions_table = "`geodesic_classifieds_user_subscriptions`";
    const subscription_choices = "`geodesic_classifieds_subscription_choices`";
    const font_page_table = "`geodesic_font_pages`";
    const font_sub_page_table = "`geodesic_font_subpages`";
    const font_element_table = "`geodesic_font_elements`";
    const paypal_transaction_table = "`geodesic_paypal_transactions`";
    const cc_choices = "`geodesic_credit_card_choices`";
    const sell_table = "`geodesic_classifieds_sell_session`";
    const cart = "`geodesic_cart`";
    const cart_registry = "`geodesic_cart_registry`";
    const registration_table = "`geodesic_registration_session`";
    const session_table = '`geodesic_sessions`';
    const session_registry = '`geodesic_sessions_registry`';
    const banners_table = "`geodesic_banners`";
    const currency_types_table = "`geodesic_currency_types`";
    const worldpay_configuration_table = "`geodesic_worldpay_settings`";
    const worldpay_transaction_table = "`geodesic_worldpay_transactions`";
    const registration_configuration_table = "`geodesic_registration_configuration`";
    const registration_choices_table = "`geodesic_registration_question_choices`";
    const registration_choices_types_table = "`geodesic_registration_question_types`";
    const price_plan_lengths_table = "`geodesic_price_plan_ad_lengths`";
    const subscription_holds_table = "`geodesic_classifieds_user_subscriptions_holds`";
    const voting_table = "`geodesic_classifieds_votes`";
    const attached_price_plans = "`geodesic_group_attached_price_plans`";
    const balance_transactions = "`geodesic_balance_transactions`";
    const balance_transactions_items = "`geodesic_balance_transactions_items`";
    const invoices_table = "`geodesic_invoices`";
    const nochex_transaction_table = "`geodesic_nochex_transactions`";
    const nochex_settings_table = "`geodesic_nochex`";
    const auction_payment_types_table = "`geodesic_payment_types`";
    const auctions_expired_table = "`geodesic_auctions_expired`";
    const email_queue_table = "`geodesic_email_queue`";
    const site_settings_table = "`geodesic_site_settings`";
    const site_settings_long_table = "`geodesic_site_settings_long`";
    const pages_table = "`geodesic_pages`";
    const pages_sections_table = "`geodesic_pages_sections`";
    const pages_text_table = "`geodesic_pages_messages`";
    const pages_text_languages_table = "`geodesic_pages_messages_languages`";
    const pages_languages_table = "`geodesic_pages_languages`";
    const block_email_domains = "`geodesic_email_domains`";
    const final_fee_table = "`geodesic_auctions_final_fee_price_increments`";
    const bid_table = "`geodesic_auctions_bids`";
    const autobid_table = "`geodesic_auctions_autobids`";
    const increments_table = "`geodesic_auctions_increments`";
    const auctions_feedbacks_table = "`geodesic_auctions_feedbacks`";
    const auctions_feedback_icons_table = "`geodesic_auctions_feedback_icons`";
    const blacklist_table = "`geodesic_auctions_blacklisted_users`";
    const invitedlist_table = "`geodesic_auctions_invited_users`";
    const postal_code_table = "`geodesic_zip_codes`";
    const field_session_id = 'classified_session';
    const ip_ban_table = '`geodesic_banned_ips`';
    const version_table = '`geodesic_version`';
    const addon_table = '`geodesic_addons`';
    const addon_text_table = '`geodesic_addon_text`';
    const classified_groups_table = "`geodesic_groups`";
    const fields = "`geodesic_fields`";
    const field_locations = "`geodesic_field_locations`";
    const tags = "`geodesic_listing_tags`";
    const offsite_videos = "`geodesic_listing_offsite_videos`";
    const form_messages = "`geodesic_classifieds_messages_form`";
    const print_publication = "`geodesic_print_publication`";
    const print_publication_lang = "`geodesic_print_publication_languages`";
    const print_publish_days = "`geodesic_print_publish_days`";
    const combined_css_list = "`geodesic_combined_css_list`";
    const combined_js_list = "`geodesic_combined_js_list`";

    const region = "`geodesic_region`";
    const region_languages = "`geodesic_region_languages`";
    const region_level = "`geodesic_region_level`";
    const region_level_labels = "`geodesic_region_level_labels`";

    const listing_regions = "`geodesic_listing_regions`";
    const user_regions = "`geodesic_user_regions`";

    const leveled_fields = "`geodesic_leveled_fields`";
    const leveled_field_value = "`geodesic_leveled_field_value`";
    const leveled_field_value_languages = "`geodesic_leveled_field_value_languages`";

    const leveled_field_level = "`geodesic_leveled_field_level`";
    const leveled_field_level_labels = "`geodesic_leveled_field_level_labels`";

    const listing_leveled_fields = "`geodesic_listing_leveled_fields`";

    const listing_cost_option_group = "`geodesic_listing_cost_option_group`";
    const listing_cost_option = "`geodesic_listing_cost_option`";
    const listing_cost_option_quantity = "`geodesic_listing_cost_options_quantity`";
    const listing_cost_option_q_option = "`geodesic_listing_cost_options_q_option`";

    //order/invoice system
    const order = '`geodesic_order`';
    const order_registry = '`geodesic_order_registry`';
    const order_item = '`geodesic_order_item`';
    const order_item_registry = '`geodesic_order_item_registry`';
    const plan_item = '`geodesic_plan_item`';
    const plan_item_registry = '`geodesic_plan_item_registry`';
    const invoice = '`geodesic_invoice`';
    const invoice_registry = '`geodesic_invoice_registry`';
    const transaction = '`geodesic_transaction`';
    const transaction_registry = '`geodesic_transaction_registry`';
    const payment_gateway = '`geodesic_payment_gateway`';
    const payment_gateway_registry = '`geodesic_payment_gateway_registry`';
    const recurring_billing = '`geodesic_recurring_billing`';
    const recurring_billing_registry = '`geodesic_recurring_billing_registry`';

    const browsing_filters = '`geodesic_browsing_filters`';
    const browsing_filters_settings = '`geodesic_browsing_filters_settings`';
    const browsing_filters_settings_languages = '`geodesic_browsing_filters_settings_languages`';

    const jit_confirm = '`geodesic_jit_confirmations`';

    const user_ratings = '`geodesic_user_ratings`';
    const user_ratings_averages = '`geodesic_user_ratings_averages`';

    const listingextra_duration_prices = '`geodesic_listingextra_duration_prices`';
    const listingextra_duration_languages = '`geodesic_listingextra_duration_languages`';
    const listingextra_expirations = '`geodesic_listingextra_expirations`';

    const listing_subscription_lengths = "`geodesic_listing_subscription_lengths`";
    const listing_subscription = "`geodesic_listing_subscription`";

    /**
     * Overloaded function, to allow $geoTables->table_var syntax.
     * @param String
     * @return String
     */
    public function __get($name)
    {
        //make sure it is only alpha-numeric and underscores.
        $table = constant('geoTables::' . $name);
        return ($table);
    }
}

/**
 * Used so that old locations that use the old name geoTables will still work.
 * @package System
 */
class metaDbTables extends geoTables
{
}
