const app = getApp();

Page({
  data: {
    level: 1,
    idioms: [],
    showDetail: false,
    selectedIdiom: null
  },

  onLoad: function (options) {
    var level = parseInt(options.level) || 1;
    var idioms = [];
    try {
      if (options.idioms) {
        idioms = JSON.parse(decodeURIComponent(options.idioms));
      }
    } catch (e) {
      console.error('解析成语数据失败:', e);
    }

    this.setData({
      level: level,
      idioms: idioms
    });
  },

  viewIdiomDetail: function (e) {
    var index = e.currentTarget.dataset.index;
    var idiom = this.data.idioms[index];
    wx.navigateTo({
      url: '/pages/idiom-detail/idiom-detail?idiom=' + encodeURIComponent(JSON.stringify(idiom))
    });
  },

  nextLevel: function () {
    var nextLevel = this.data.level + 1;
    wx.redirectTo({
      url: '/pages/game/game?level=' + nextLevel
    });
  },

  backToHome: function () {
    wx.navigateBack({
      delta: 2
    });
  },

  onShareAppMessage: function (e) {
    var idiom = this.data.selectedIdiom;
    if (e.target && e.target.dataset.idiom) {
      idiom = e.target.dataset.idiom;
    }
    
    if (idiom) {
      return {
        title: '成语闯关 - ' + idiom.idiom,
        path: '/pages/index/index',
        imageUrl: '/images/share.png'
      };
    }

    return {
      title: '成语闯关 - 第' + this.data.level + '关已通过',
      path: '/pages/index/index',
      imageUrl: '/images/share.png'
    };
  }
});
