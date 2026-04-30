const app = getApp();

Page({
  data: {
    userInfo: null,
    currentLevel: 1,
    totalLevels: 100,
    completedLevels: 0,
    notebookCount: 0
  },

  onLoad: function () {
    this.loadUserInfo();
  },

  onShow: function () {
    this.loadUserInfo();
  },

  loadUserInfo: function () {
    var that = this;
    if (app.globalData.userInfo) {
      this.setData({
        userInfo: app.globalData.userInfo,
        currentLevel: app.globalData.userInfo.current_level || 1,
        completedLevels: app.globalData.userInfo.completed_levels || 0
      });
      this.loadNotebookCount();
    } else {
      setTimeout(function () {
        that.loadUserInfo();
      }, 500);
    }
  },

  loadNotebookCount: function () {
    var that = this;
    app.request({
      url: '/notebook.php',
      method: 'GET',
      data: {
        action: 'count'
      }
    }).then(function (res) {
      if (res.data.status === 'success') {
        that.setData({
          notebookCount: res.data.data.count
        });
      }
    });
  },

  startGame: function () {
    wx.navigateTo({
      url: '/pages/game/game?level=' + this.data.currentLevel
    });
  },

  goToNotebook: function () {
    wx.navigateTo({
      url: '/pages/notebook/notebook'
    });
  }
});
