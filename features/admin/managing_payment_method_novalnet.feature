@managing_payment_method_novalnet
Feature: Adding a new payment method
    In order to pay for orders in different ways
    As an Administrator
    I want to add a new payment method to the registry

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui
    Scenario: Adding a new Novalnet payment method
        Given I want to create a new Novalnet payment method
        When I name it "Novalnet AG" in "English (United States)"
        And I specify its code as "novalnet"
        And I configure it with test Novalnet credentials
        And I add it
        Then I should be notified that it has been successfully created
        And the payment method "Novalnet AG" should appear in the registry
