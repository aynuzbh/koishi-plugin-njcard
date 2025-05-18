"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.apply = exports.Config = exports.usage = exports.name = void 0;
const koishi_1 = require("koishi");
exports.name = "皇冠抽卡";
exports.usage = "一个用于皇冠抽卡的插件";
exports.Config = koishi_1.Schema.object({});

function apply(ctx) {
    // 单抽命令
    ctx.command("单抽", "妮姬单抽")
        .action(async ({ session }) => {
            try {
                const response = await ctx.http.get("https://nj.aynu.asia/index.php?count=1&format=text");
                const imageUrl = response.trim(); // 去除可能的空白字符
                return `${session?.username || '你'}的抽卡结果：\n` + 
                       koishi_1.segment.image("https:" + imageUrl);
            } catch (error) {
                return "抽卡失败，请稍后再试";
            }
        });

    // 十连抽命令
    ctx.command("十连", "妮姬十连")
        .action(async ({ session }) => {
            try {
                const response = await ctx.http.get("https://nj.aynu.asia/index.php?count=10&format=text");
                const imageUrls = response.trim().split("\n");
                
                let result = `${session?.username || '你'}的十连抽卡结果：\n`;
                for (const url of imageUrls) {
                    result += koishi_1.segment.image("https:" + url) + "\n";
                }
                return result;
            } catch (error) {
                return "十连抽失败，请稍后再试";
            }
        });
}
exports.apply = apply;