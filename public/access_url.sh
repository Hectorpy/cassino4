#!/bin/bash

while true; do
  # 使用curl或wget等命令访问URL
  # 这里使用curl作为示例
  curl -s https://api.rk2020.com/api/index/game_record
  
  sleep 65  # 休眠65秒（60秒 + 5秒），实现每1分5秒执行一次
done