##############################################
#                   VPC                      #
##############################################
module "vpc" {
  source          = "../../modules/vpc"
  cluster_name    = var.cluster_name
  private_subnets = var.private_subnets
  public_subnets  = var.public_subnets
  azs             = var.azs
  vpc_cidr        = var.vpc_cidr
  region          = var.region
  tags            = var.tags
}

##############################################
#                   EKS                      #
##############################################
module "eks" {
  source         = "../../modules/eks"
  cluster_name   = var.cluster_name
  instance_types = ["t3.medium"]
  capacity_type  = "SPOT"
  min_size       = 1
  max_size       = 2
  desired_size   = 1

  tags                     = var.tags
  vpc_id                   = module.vpc.vpc_id
  subnet_ids               = module.vpc.private_subnets
  control_plane_subnet_ids = module.vpc.public_subnets
}

##############################################
#                   IAM                      #
##############################################
module "iam" {
  source       = "../../modules/iam"
  provider_arn = module.eks.provider_arn
  tags         = var.tags
}

##############################################
#               ArgoCD                       #
##############################################
# resource "kubernetes_manifest" "argocd" {
#   manifest = {
#     "apiVersion" = "v1"
#     "kind"       = "Namespace"
#     "metadata" = {
#       "labels" = {
#         "name" = "argocd"
#       }
#       "name" = "argocd"
#     }
#   }
# }
# module "argocd" {
#   source     = "../../modules/helm"
#   repository = var.argocd_helm_repository
#   namespace  = var.argocd_helm_namespace
#   app = {
#     name             = var.argocd_helm_name
#     chart            = var.argocd_helm_chart
#     deploy           = 1
#     create_namespace = true
#     version          = null
#   }
#   values     = [file("helm_values/argocd/argo.yaml")]
#   depends_on = [kubernetes_manifest.argocd, module.eks]
# }

resource "null_resource" "clean_up_argocd_resources" {
  triggers = {
    eks_cluster_name = var.cluster_name
  }
  provisioner "local-exec" {
    command     = <<-EOT
      kubeconfig=/tmp/tf.clean_up_argocd.kubeconfig.yaml
      aws eks update-kubeconfig --name ${self.triggers.eks_cluster_name} --kubeconfig $kubeconfig
      rm -f /tmp/tf.clean_up_argocd_resources.err.log
      kubectl --kubeconfig $kubeconfig get Application -A -o name | xargs -I {} kubectl --kubeconfig $kubeconfig -n argocd patch -p '{"metadata":{"finalizers":null}}' --type=merge {} 2> /tmp/tf.clean_up_argocd_resources.err.log || true
      rm -f $kubeconfig
    EOT
    interpreter = ["bash", "-c"]
    when        = destroy
  }
}