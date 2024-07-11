# FitPro App - Backend and Infrastructure

<div align="center">
<img src="https://github.com/smadi0x86/Fitpro-app/assets/75253629/fbe172bd-0b59-40cf-89c2-87c8eaa814f2">
</div>

<p align="center">
  <a href="https://github.com/smadi0x86/Website-D-D-Final/actions/workflows/frontend-ci.yml">
    <img src="https://github.com/smadi0x86/Fitpro-app/actions/workflows/frontend-ci.yml/badge.svg" alt="Frontend CI/CD">
  </a>
  <a href="https://github.com/smadi0x86/Website-D-D-Final/actions/workflows/backend-ci.yml">
    <img src="https://github.com/smadi0x86/Fitpro-app/actions/workflows/backend-ci.yml/badge.svg" alt="Backend CI/CD">
  </a>
</p>

## Overview
**FitPro is a dynamic, responsive fitness management application that bridges the gap between fitness enthusiasts and their health goals.**

**It integrates a user-friendly interface, robust backend services, and a reliable cloud infrastructure to deliver an unmatched digital fitness experience.**

## Backend
**The backbone of FitPro, the backend is engineered using PHP with the OpenSwoole framework, enhancing the application with asynchronous processing capabilities.**

**This design choice ensures non-blocking I/O operations, crucial for tasks like email verification and user authentication, providing a smooth user experience.**

### Key Features:

- **Email Verification:** Development phase utilizes Mailhog, transitioning to SendGrid for production to ensure reliable email delivery.
- **JWT Middleware:** Manages authentication tokens with a 24-hour expiry for enhanced security.
- **Stripe Integration:** Handles secure payment transactions for memberships and product purchases.
- **Logging and Diagnostics:** Comprehensive logging into files for real-time debugging.
- **API Documentation:** Leveraging Swagger for well-documented and testable API endpoints.
  
## Kubernetes & Cloud Infrastructure
**FitPro's infrastructure is architected to be resilient, scalable, and secure, utilizing Kubernetes orchestration and AWS services.**

### Kubernetes:

- **Pods Management:** Utilizes a multi-pod design with separate pods for the frontend, backend, and PostgreSQL database.
- **Persistent Volume Claims (PVCs):** Ensures data persistence for the PostgreSQL database, safeguarding against data loss.
- **ConfigMaps & Secrets:** Manages environment variables and sensitive information, respectively, across services.
- **Ingress Controllers:** Implements load balancing and SSL termination, crucial for handling incoming traffic efficiently.
  
### Continuous Integration and Deployment (CI/CD):

- **GitHub Actions:** Automates the pipeline for testing, building, and pushing Docker images to AWS Elastic Container Registry (ECR).
- **ArgoCD:** Facilitates GitOps by syncing Kubernetes manifests from the GitHub repository to the live cluster, ensuring consistency.
  
### Cloud Services:

- **AWS EKS:** Provides a managed Kubernetes service for running the containers, ensuring high availability and scalability.
- **AWS Route53 & Certificate Manager:** Integrates DNS management with SSL/TLS encryption, establishing a secure and trusted domain for the application.

### Monitoring & Observability:

- **Grafana and Prometheus:** Delivers a comprehensive monitoring solution, providing insights into application performance and health metrics.
  
### DevOps Best Practices:

- **Terraform:** Employs Infrastructure as Code (IaC) for provisioning and managing cloud resources in a repeatable and predictable manner.
- **Security Scanning:** Implements Kubescape for scanning Kubernetes configurations against security best practices and compliance checks.
  

## References

- https://artifacthub.io/packages/helm/ingress-nginx/ingress-nginx
- https://artifacthub.io/packages/helm/cert-manager/cert-manager?modal=values&path=serviceAccount
- https://kubernetes.io/
- https://docs.aws.amazon.com/eks/latest/userguide/getting-started.html
- https://registry.terraform.io/modules/terraform-aws-modules/eks/aws/latest
- https://registry.terraform.io/modules/terraform-aws-modules/vpc/aws/latest
- https://registry.terraform.io/providers/hashicorp/helm/latest/docs/resources/release
- https://argo-cd.readthedocs.io/en/stable/user-guide/helm/
- https://registry.terraform.io/modules/streamnative/charts/helm/latest/submodules/prometheus-operator
