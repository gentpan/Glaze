// Shapes use the Kubai icon grid. A single ink color defines the face, details and outline.
export function blogIcons({ rr }) {
  const path = (d) => `<path d="${d}"/>`;
  const circle = (cx, cy, r) => `<circle cx="${cx}" cy="${cy}" r="${r}"/>`;
  const surface = (d, opacity = 0.16) =>
    `<path d="${d}" fill="currentColor" fill-opacity="${opacity}"/>`;
  const panel = () => surface(rr(4, 3.3, 23, 18.4, 2.2));
  const square = () => surface(rr(5.2, 2.2, 20.6, 20.6, 2.2));
  const page = () => surface('M6.3 3.6Q6.3 1 8.9 1h10l5.8 5.8v14.6Q24.7 24 22.1 24H8.9q-2.6 0-2.6-2.6Z')
    + surface('M18.9 1v5.8h5.8', 0.25);
  const box = (d) => panel() + path(d);
  const tile = (d) => square() + path(d);
  const paper = (d) => page() + path(d);
  const bubble = (d) => surface('M6 3.7h19a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H13l-6 4v-4H6a2 2 0 0 1-2-2v-12a2 2 0 0 1 2-2Z') + path(d);
  const openBook = (line) => surface('M4 4.5h7a6 6 0 0 1 4.5 2 6 6 0 0 1 4.5-2h7v16h-7a6 6 0 0 0-4.5 2 6 6 0 0 0-4.5-2H4Z') + path('M15.5 7v15.5' + line);
  const tag = () => surface('M5 4.5h10l11 11-8 8-13-13Z') + circle(10, 9.3, 1.4);
  const phone = (mark) => surface(rr(8, 1, 15, 23, 2.2)) + path('M13 5h5M13 20h5' + mark);
  const heart = () => surface('M15.5 22 5.6 12.5a5.8 5.8 0 0 1 8.2-8.2l1.7 1.7 1.7-1.7a5.8 5.8 0 0 1 8.2 8.2Z', 0.2);

  return {
    pages: [
      ['home', 'Home', '首页', surface('M4 11.8 14.6 3.2a1.4 1.4 0 0 1 1.8 0L27 11.8h-3v9a1.7 1.7 0 0 1-1.7 1.7H8.7A1.7 1.7 0 0 1 7 20.8v-9Z') + path('M12.6 22.5v-6.7a1 1 0 0 1 1-1h3.8a1 1 0 0 1 1 1v6.7')],
      ['about', 'About', '关于', circle(15.5, 12.5, 11) + circle(15.5, 7.7, 1.1) + path('M15.5 12v5.5m-2 0h4')],
      ['archives', 'Archives', '归档', surface(rr(6, 7.5, 19, 15.2, 2.2)) + surface(rr(4, 2.3, 23, 5.5, 1.2), 0.26) + path('M12 13h7')],
      ['links', 'Blogroll', '友情链接', path('M12.3 13.8a4.2 4.2 0 0 0 6 .1l5.2-5.1a4.3 4.3 0 0 0-6-6l-2.4 2.4m3.6 6.1a4.2 4.2 0 0 0-6-.1l-5.2 5.1a4.3 4.3 0 0 0 6 6l2.4-2.4')],
      ['feeds', 'RSS', '订阅', tile('M10 8a12 12 0 0 1 12 12M10 13a7 7 0 0 1 7 7') + circle(10.5, 19.5, 1.15)],
      ['album', 'Gallery', '相册', surface(rr(6, 1.5, 19, 17, 2.2)) + panel() + circle(20.8, 9.6, 2) + path('m4 17 7-6 6 5.5 3-2 7 6')],
      ['stats', 'Statistics', '统计', box('M8 18h15M10 15v-3m5 3V8m5 7v-5')],
      ['analytics', 'Analytics', '分析', tile('M9 17.9V7.5m0 10.4h13.2m-10-4 3.5-3.5 2.8 2.2 3.6-4.5')],
      ['categories', 'Categories', '分类', surface('M4 6a2 2 0 0 1 2-2h6l2.5 3H25a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z') + surface(rr(5.8, 10.7, 19.4, 11.3, 1.6), 0.18)],
      ['tags', 'Tags', '标签', tag() + path('m14 11 5 5')],
      ['talks', 'Short notes', '说说', bubble('M9 9.5h13M9 14h9')],
      ['guestbook', 'Guestbook', '留言板', openBook('M8 11h4m7 0h4M8 15h4m7 0h4')],
      ['contact', 'Contact', '联系', box('m4.6 6.3 10.9 8.4 10.9-8.4m-21 14 6.8-6m14.4 6-6.8-6')],
      ['projects', 'Projects', '项目', surface(rr(4, 7, 23, 15, 2.2)) + path('M11 7V3h9v4m-16 7h23') + surface(rr(13, 12.4, 5, 4, 1.2), 0.25)],
      ['search', 'Search', '搜索', circle(13, 10.8, 7.5) + circle(13, 10.8, 4.5) + path('m18.7 16.7 6.7 6.7')],
      ['bookshelf', 'Bookshelf', '书架', box('M8 18h15M9 15V7h4v8m3-1v-7h3l3 7')],
      ['now', 'Now', '近况', circle(15.5, 12.5, 11) + circle(15.5, 12.5, 7.9) + path('M15.5 7v6h4.5')],
      ['uses', 'Uses', '装备', box('M8 8.5h15v7.5H8zm4 10h7M11.2 12.3h1m2.8 0h1m2.8 0h1')],
      ['sitemap', 'Sitemap', '站点地图', tile('M12 7h7v4h-7zm3.5 4v3m-6 1.2v-1.2h12v1.2M7.2 16h5v3.4h-5zm11.6 0h5v3.4h-5z')],
      ['download', 'Downloads', '下载', surface(rr(4, 13, 23, 9.5, 2.2)) + path('M15.5 2.2v13m-4.5-4.4 4.5 4.5 4.5-4.5M9 18.4h13')],
      ['video', 'Videos', '视频', box('M13 8.5v8l7-4z')],
      ['podcast', 'Podcast', '播客', surface(rr(12, 2, 7, 14.5, 3.5)) + path('M8.5 11v2a7 7 0 0 0 14 0v-2m-7 9v3m-4 0h8')],
      ['portfolio', 'Portfolio', '作品集', box('M8 8h6v9h-6zm9 0h6v3h-6zm0 6h6v3h-6z')],
      ['updates', 'Changelog', '更新记录', paper('M10.4 12.1l1.4 1.4 2.4-2.5m3 1.4h4m-10.8 5 1.4 1.4 2.4-2.5m3 1.4h4')],
      ['faq', 'FAQ', '常见问题', bubble('M12.5 9.2a3.2 3.2 0 1 1 6.4 0c0 2-3.4 2.1-3.4 4.3') + circle(15.5, 16.4, 0.85)],
      ['privacy', 'Privacy', '隐私', surface('M15.5 1.2 25 5v7.2c0 5.5-3.8 9.5-9.5 11.5C9.8 21.7 6 17.7 6 12.2V5Z') + path('m11.5 12.5 3 3 5-5.5')],
      ['terms', 'Terms', '使用条款', paper('M10 10h5m-5 4h11m-11 4h9')],
      ['donate', 'Support', '支持赞助', heart() + path('M12 12.5h7m-3.5-3.5v7')],
      ['newsletter', 'Newsletter', '邮件通讯', box('M8 7h15v11H8zm3 3h9m-9 3h9m-9 3h5')],
      ['account', 'Account', '账户', circle(15.5, 12.5, 11) + circle(15.5, 9, 3.4) + path('M8 20a7.7 7.7 0 0 1 15 0')],
    ],
    categories: [
      ['music-sharing', 'Music', '音乐分享', square() + path('M13 18.4V7l9-2v10.9M13 9.7l9-2') + circle(10.5, 18.5, 2.5) + circle(19.5, 16, 2.5)],
      ['coding', 'Code', '日常代码', box('m11.6 9-4 3.5 4 3.5m7.8-7 4 3.5-4 3.5m-2.5-8-2.8 9')],
      ['life', 'Life', '生活日常', surface('M6 6.5h14v8.2a6.4 6.4 0 0 1-6.4 6.4h-1.2A6.4 6.4 0 0 1 6 14.7Z') + path('M20 8h2.3a3.2 3.2 0 1 1 0 6.4H20M5 23h19m-13-19V2m5 2V2')],
      ['travel', 'Travel', '旅行游记', surface(rr(5, 6, 21, 16.5, 2.2)) + path('M11 6V3h9v3M10 6v16.5m11-16.5v16.5')],
      ['misc', 'Miscellany', '杂记', box('M8 8h7v4H8zm11.4 0h3m-3 4h3M8 16.7h14.4')],
      ['mood', 'Mood', '心情笔记', heart() + path('M9.5 13h3l2-3 2.1 5.5 2.1-2.5h2.8')],
      ['photography', 'Photography', '摄影', surface('M4 8h5l2.7-4.4h7.6L22 8h5v13H4Z') + circle(15.5, 14.2, 4.5)],
      ['reading', 'Reading', '阅读书摘', openBook('M8 11l4 1m7-1 4-1M8 15l4 1')],
      ['film', 'Film & TV', '影视', surface(rr(4, 9, 23, 13.3, 2.2)) + surface('M4 9 5 3.5 26.4 1l.6 5.1Z', 0.26) + path('m9 3.1 4 4.8m5-5.8 4 4.6M9 15h13')],
      ['gaming', 'Games', '游戏', surface('M10 6.5h11a5 5 0 0 1 4.6 4l1.4 6.7a3.3 3.3 0 0 1-5.7 2.6L19 17h-7l-2.3 2.8A3.3 3.3 0 0 1 4 17.2l1.4-6.7a5 5 0 0 1 4.6-4Z') + path('M9 12h5m-2.5-2.5v5') + circle(20.5, 11, 0.8) + circle(22.8, 13.7, 0.8)],
      ['food', 'Food', '美食', circle(15.5, 12.5, 10.4) + circle(15.5, 12.5, 6.6) + path('M4.1 4v7m-2.5-7v4a2.5 2.5 0 0 0 5 0V4m-2.5 7v10m23.4 0V4l-2.3 6.7h2.3')],
      ['technology', 'Technology', '数码科技', phone('M12 9h7v7h-7z')],
      ['design', 'Design', '设计', tile('m10 17 3.4-7.5 7.4-2.6-2.6 7.4L10 17zm0 0 6-6m-5 7h8')],
      ['work', 'Work', '工作', surface(rr(4, 6, 23, 16, 2.2)) + path('M11 6V3h9v3m-16 7h23m-13.5 3h4v3h-4z')],
      ['learning', 'Learning', '学习', surface('M4 8.8 15.5 3 27 8.8l-11.5 6Z') + path('M8 11v7c5 4.5 10 4.5 15 0v-7m4-2v7')],
      ['fitness', 'Fitness', '运动健身', square() + path('m8.8 17.7 8.9-10m-5.6 12.8 8.9-10M7.7 14l6.6 6m2.4-15.3 6.6 6m-10.2 2.2 4.6 4.1')],
      ['nature', 'Outdoors', '自然户外', surface('m4 21.5 8-14 4.2 6 3-4.1L27 21.5Z') + path('M10 11.2l2 3.4 2.3-2.2m3.1.1 2 3 2.1-1.7') + circle(21, 4.5, 2.2)],
      ['ai', 'AI', '人工智能', surface(rr(5.2, 4.2, 20.6, 16.6, 2.2)) + path('M15.5 1v3.2m-13 7h2.7m20.6 0h2.7m-16 5.3h6') + circle(11, 10.8, 1.4) + circle(20, 10.8, 1.4)],
      ['daily-log', 'Diary', '日记记录', paper('M11 10v9m4-9h5m-5 4h5m-5 4h3')],
      ['tutorials', 'Tutorials', '教程经验', panel() + path('M8 8h15m-15 4h15m-15 4h7m3 1.5 1.6 1.7 3.2-3.2')],
    ],
  };
}
