<?php
/**
 * PHPUnit Tests for Subscriptions Module
 */

use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/../../includes/subscriptions_db.inc';

class SubscriptionsDBTest extends TestCase
{
    private $mockDb;
    
    protected function setUp(): void
    {
        global $db;
        $this->mockDb = new MockDB();
        $db = $this->mockDb;
    }
    
    protected function tearDown(): void
    {
        global $db;
        $db = null;
    }
    
    public function testCreateSubscriptionTemplate(): void
    {
        $result = create_subscription_template('Basic Plan', 'fixed', 99.00, 'monthly');
        
        $this->assertEquals(1, $result);
    }
    
    public function testCreateOnDemandTemplate(): void
    {
        $result = create_subscription_template('Usage Plan', 'on_demand');
        
        $this->assertEquals(1, $result);
    }
    
    public function testSubscribeCustomer(): void
    {
        $result = subscribe_customer(5, 1, '2024-01-01');
        
        $this->assertEquals(1, $result);
    }
    
    public function testSubscribeCustomerDefaultDate(): void
    {
        $result = subscribe_customer(5, 1);
        
        $this->assertEquals(1, $result);
    }
    
    public function testGetSubscriptionsNoFilters(): void
    {
        $result = get_subscriptions([]);
        
        $this->assertNotFalse($result);
    }
    
    public function testGetSubscriptionsByCustomer(): void
    {
        $result = get_subscriptions(['customer_id' => 5]);
        
        $this->assertNotFalse($result);
    }
    
    public function testGetSubscriptionsByStatus(): void
    {
        $result = get_subscriptions(['status' => 'active']);
        
        $this->assertNotFalse($result);
    }
    
    public function testRecordUsage(): void
    {
        $result = record_usage(1, 'api_calls', 1000, 0.001, 'API Usage');
        
        $this->assertEquals(1, $result);
    }
    
    public function testRecordUsageWithoutDescription(): void
    {
        $result = record_usage(1, 'storage', 10, 0.10);
        
        $this->assertEquals(1, $result);
    }
    
    public function testGetUsageUnbilled(): void
    {
        $result = get_usage_unbilled(1);
        
        $this->assertNotFalse($result);
    }
    
    public function testBillUsage(): void
    {
        $result = bill_usage(1);
        
        $this->assertGreaterThanOrEqual(0, $result);
    }
    
    public function testCancelSubscription(): void
    {
        $result = cancel_subscription(1);
        
        $this->assertTrue($result);
    }
    
    public function testProcessDueSubscriptions(): void
    {
        $result = process_due_subscriptions();
        
        $this->assertGreaterThanOrEqual(0, $result);
    }
    
    public function testCalculateNextBillingMonthly(): void
    {
        $result = calculate_next_billing_date('monthly');
        
        $this->assertNotEmpty($result);
    }
    
    public function testCalculateNextBillingQuarterly(): void
    {
        $result = calculate_next_billing_date('quarterly');
        
        $this->assertNotEmpty($result);
    }
    
    public function testCalculateNextBillingAnnually(): void
    {
        $result = calculate_next_billing_date('annually');
        
        $this->assertNotEmpty($result);
    }
}