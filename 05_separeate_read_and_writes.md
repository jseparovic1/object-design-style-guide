Separate read and writes is useful technic to make your code efficient and easier to change.

Consider following example

```php
class PurchaseOrder
{
    private int $orderId;
    private int $productId;
    private int $qantity;
    private int $wasReceived;
    
    public static function place(int $order, int $product, int $quantity): self
    {
        $purchase = new PurchaseOrder();
        $purchase->orderId = $order;
        $purchase->productId = $product;
        $purchase->qantity = $quantity;
        $purchase->wasReceived = $false;
        
        // Dispatching events and recording them is also an option
        // We can store series of event and then determine and calculate reports based on that
        // This technique is called EventSourcing
        $this->events->dispatch(new OrderPruchased($purchase));
        
        return $purchase;
    }
    
    // Same getters needed in order to expose data
    public function getOrderId(): int
    {
        return $this->orderId;
    }
    
    // It is possible also to fill read model from the write model
    public function getReport(): PurchaseReport
    {
        return new PurchaseReport(
            $this->orderId,
            $this->productId,
            $this->qantity,
            $this->wasReceived
        )
    }
}

// In order to avoid exposing information that services not needed
// separate read model can be created

class PurchaseReportsRepository
{
    public function getForOrder(int $id): PurchaseReport
    {
        $rawReport = $this->connection->execute(
        <<<SQL
            SELECT * from purchase_orders;    
SQL
        )
        
        return new PurchaseReport::fromRaw($rawReport);
    }
}
```

