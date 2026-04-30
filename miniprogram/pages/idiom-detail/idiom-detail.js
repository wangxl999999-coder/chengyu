const app = getApp();

Page({
  data: {
    idiom: null,
    isInNotebook: false,
    showReportModal: false
  },

  onLoad: function (options) {
    var idiom = null;
    try {
      if (options.idiom) {
        idiom = JSON.parse(decodeURIComponent(options.idiom));
      }
    } catch (e) {
      console.error('解析成语数据失败:', e);
      wx.showToast({
        title: '数据错误',
        icon: 'none'
      });
    }

    if (idiom) {
      this.setData({ idiom: idiom });
      this.checkNotebook(idiom.id);
    }
  },

  checkNotebook: function (idiomId) {
    var that = this;
    app.request({
      url: '/notebook.php',
      method: 'GET',
      data: {
        action: 'check',
        idiom_id: idiomId
      }
    }).then(function (res) {
      if (res.data.status === 'success') {
        that.setData({
          isInNotebook: res.data.data.exists
        });
      }
    });
  },

  toggleNotebook: function () {
    var that = this;
    var idiom = this.data.idiom;
    var action = this.data.isInNotebook ? 'remove' : 'add';

    app.request({
      url: '/notebook.php',
      method: 'POST',
      data: {
        action: action,
        idiom_id: idiom.id,
        idiom: idiom.idiom,
        pinyin: idiom.pinyin,
        explanation: idiom.explanation,
        source: idiom.source,
        example: idiom.example
      }
    }).then(function (res) {
      if (res.data.status === 'success') {
        that.setData({
          isInNotebook: !that.data.isInNotebook
        });
        wx.showToast({
          title: that.data.isInNotebook ? '已加入生词本' : '已移出生词本',
          icon: 'success'
        });
      }
    });
  },

  showReport: function () {
    this.setData({
      showReportModal: true
    });
  },

  hideReport: function () {
    this.setData({
      showReportModal: false
    });
  },

  submitReport: function (e) {
    var that = this;
    var content = e.detail.value.content.trim();

    if (!content) {
      wx.showToast({
        title: '请输入错误描述',
        icon: 'none'
      });
      return;
    }

    wx.showLoading({ title: '提交中...' });

    app.request({
      url: '/report.php',
      method: 'POST',
      data: {
        idiom_id: this.data.idiom.id,
        idiom: this.data.idiom.idiom,
        content: content
      }
    }).then(function (res) {
      wx.hideLoading();
      if (res.data.status === 'success') {
        wx.showToast({
          title: '提交成功',
          icon: 'success'
        });
        that.hideReport();
      } else {
        wx.showToast({
          title: '提交失败',
          icon: 'none'
        });
      }
    }).catch(function () {
      wx.hideLoading();
      wx.showToast({
        title: '网络错误',
        icon: 'none'
      });
    });
  },

  onShareAppMessage: function () {
    var idiom = this.data.idiom;
    return {
      title: '成语' + idiom.idiom + ' - ' + (idiom.pinyin || ''),
      path: '/pages/index/index',
      imageUrl: '/images/share.png'
    };
  }
});
