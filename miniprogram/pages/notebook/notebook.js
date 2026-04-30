const app = getApp();

Page({
  data: {
    notebooks: [],
    loading: false
  },

  onLoad: function () {
    this.loadNotebooks();
  },

  onShow: function () {
    this.loadNotebooks();
  },

  loadNotebooks: function () {
    var that = this;
    this.setData({ loading: true });

    app.request({
      url: '/notebook.php',
      method: 'GET',
      data: {
        action: 'list'
      }
    }).then(function (res) {
      that.setData({ loading: false });
      if (res.data.status === 'success') {
        that.setData({
          notebooks: res.data.data
        });
      }
    }).catch(function () {
      that.setData({ loading: false });
      wx.showToast({
        title: '加载失败',
        icon: 'none'
      });
    });
  },

  viewIdiomDetail: function (e) {
    var index = e.currentTarget.dataset.index;
    var notebook = this.data.notebooks[index];
    var idiom = {
      id: notebook.idiom_id,
      idiom: notebook.idiom,
      pinyin: notebook.pinyin,
      explanation: notebook.explanation,
      source: notebook.source,
      example: notebook.example
    };

    wx.navigateTo({
      url: '/pages/idiom-detail/idiom-detail?idiom=' + encodeURIComponent(JSON.stringify(idiom))
    });
  },

  removeNotebook: function (e) {
    var that = this;
    var index = e.currentTarget.dataset.index;
    var notebook = this.data.notebooks[index];

    wx.showModal({
      title: '提示',
      content: '确定要移出这个成语吗？',
      success: function (res) {
        if (res.confirm) {
          app.request({
            url: '/notebook.php',
            method: 'POST',
            data: {
              action: 'remove',
              idiom_id: notebook.idiom_id
            }
          }).then(function (res) {
            if (res.data.status === 'success') {
              var notebooks = that.data.notebooks.slice();
              notebooks.splice(index, 1);
              that.setData({ notebooks: notebooks });
              wx.showToast({
                title: '已移除',
                icon: 'success'
              });
            }
          });
        }
      }
    });
  }
});
