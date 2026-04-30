const app = getApp();

Page({
  data: {
    userInfo: null,
    currentLevel: 1,
    totalLevels: 100,
    completedLevels: 0,
    notebookCount: 0,
    isLoading: true,
    loadRetryCount: 0,
    maxRetryCount: 10
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
        completedLevels: app.globalData.userInfo.completed_levels || 0,
        isLoading: false
      });
      this.loadNotebookCount();
    } else if (this.data.loadRetryCount < this.data.maxRetryCount) {
      this.setData({
        loadRetryCount: this.data.loadRetryCount + 1
      });
      setTimeout(function () {
        that.loadUserInfo();
      }, 300);
    } else {
      console.log('加载用户信息超时，使用默认值');
      var defaultUserInfo = {
        id: 0,
        nickname: '游客',
        avatar_url: '',
        current_level: 1,
        completed_levels: 0
      };
      
      that.setData({
        userInfo: defaultUserInfo,
        currentLevel: 1,
        completedLevels: 0,
        isLoading: false
      });
      
      app.globalData.userInfo = defaultUserInfo;
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
      if (res.data && res.data.status === 'success') {
        that.setData({
          notebookCount: res.data.data.count || 0
        });
      }
    }).catch(function () {
      console.log('加载生词本数量失败');
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
