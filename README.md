Zero PHP

PHP极简框架
    
    极简
    零依赖
    极少配置
    同时支持常驻全协程和传统LNMP模式
    

代码结构
   
    app 应用逻辑
        Command 命令行脚本
        Common 常量定义
        Dao 数据操作层
            Cache 缓存操作
            Entity 数据实体
            Model 数据库操作
        Http 业务控制层
        Middleware 中间件
        Service 服务封装层
        Bootstrap.php 业务配置&初始化文件
    docker docker配置
    docs 文档
    public 静态文件目录
    runtime 运行时生成文件目录
    zero 框架类库
    
    env.yml 配置文件 
    tools 命令操作入口文件
    

奉行约定优于配置原则 低成本易维护高性能

Todo

- [ ] client 统一封装,协程/同步自动判断
- [ ] mysql&redis 客户端配置可选
- [ ] Grpc&thrift 客户端支持
- [ ] 更多平行化 明确配置
- [ ] 完整测试
