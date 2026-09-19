# Integration Architecture

## Overview
The Laravel application serves as the central **Integration Layer** connecting multiple disparate external systems. The architecture enforces strict separation of concerns, ensuring that external systems do not communicate with each other directly and are completely unaware of one another.

### Separation of Concerns
1. **ERPNext Integration**: Handled exclusively by `ErpNextService.php`.
2. **SMS Gateway Integration**: Handled exclusively by `SmppSmsService.php`.

## Architecture Diagram

```mermaid
graph TD
    subgraph Laravel Application [Integration Layer]
        L[Laravel App] --> EService[ErpNextService]
        L --> SService[SmppSmsService]
    end

    subgraph External Systems
        EService -->|Project sync / API calls| ERP[ERPNext/Frappe API\n172.16.10.231 / 239]
        SService -->|Send SMS / OTP| SMS[SMS Gateway API\n10.172.172.2:8866]
    end

    classDef laravel fill:#ff2d20,stroke:#333,stroke-width:2px,color:#fff;
    classDef service fill:#f9f9f9,stroke:#333,stroke-width:2px;
    classDef external fill:#4a90e2,stroke:#333,stroke-width:2px,color:#fff;
    
    class L laravel;
    class EService,SService service;
    class ERP,SMS external;
```

## System Rules
- **No Direct Communication**: ERPNext does not send SMS. SMS Gateway does not know about ERPNext. There is no API bridge between ERPNext and the SMS Gateway.
- **Service Isolation**: Each external system has a dedicated integration service within Laravel.
- **Troubleshooting Isolation**:
    - SMS issues should ONLY be traced within `config/sms.php`, SMPP variables in `.env`, and `SmppSmsService.php`.
    - ERPNext issues should ONLY be traced within `ErpNextService.php` and ERP variables in `.env`.
    - Modifying one service's configuration to fix the other is strictly prohibited.
