# 使用官方 PHP 7.4 CLI 镜像
FROM php:7.4-cli

# 设置工作目录
WORKDIR /app

# 拷贝当前目录所有文件进容器
COPY . .

# 暴露 Render 要求的端口
EXPOSE 10000

# 启动 PHP 内建 Web 服务
CMD ["php", "-S", "0.0.0.0:10000", "index.php"]
