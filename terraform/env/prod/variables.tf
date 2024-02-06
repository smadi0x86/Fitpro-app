####################################################################
variable "domain" {}
variable "region" {}
variable "tags" {}
####################################################################
variable "vpc_cidr" {}
variable "private_subnets" {}
variable "public_subnets" {}
variable "azs" {}
####################################################################
variable "cluster_name" {}
####################################################################
variable "argocd_helm_name" {}
variable "argocd_helm_namespace" {}
variable "argocd_helm_chart" {}
variable "argocd_helm_repository" {}
