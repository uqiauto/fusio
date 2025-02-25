
describe('App tests', function() {

  it('List app', function() {
    browser.get('#/app');

    var routes = element.all(by.repeater('app in apps'));
    expect(routes.count()).toEqual(4);
    expect(routes.get(0).getText()).toMatch('Deactivated');
    expect(routes.get(1).getText()).toMatch('Pending');
    expect(routes.get(2).getText()).toMatch('Foo-App');
    expect(routes.get(3).getText()).toMatch('Backend');
  });

  it('Create app', function() {
    browser.get('#/app');

    var EC = protractor.ExpectedConditions;

    $('a.btn-primary').click();

    browser.wait(EC.visibilityOf($('div.modal-body')), 5000);

    var statusOptions = element.all(by.options('state.key as state.value for state in states'));
    expect(statusOptions.get(0).getText()).toEqual('Active');
    expect(statusOptions.get(1).getText()).toEqual('Pending');
    expect(statusOptions.get(2).getText()).toEqual('Deactivated');
    statusOptions.get(0).click();

    element(by.id('user')).sendKeys('Develop');
    browser.wait(EC.visibilityOf($('.dropdown-menu[typeahead-popup]')), 5000);
    element.all(by.css('.dropdown-menu[typeahead-popup] li a')).first().click();

    element(by.model('app.name')).sendKeys('test-app');
    element(by.model('app.url')).sendKeys('http://foo.com');

    $('button.btn-primary').click();

    browser.wait(EC.visibilityOf($('div.alert-success')), 5000);

    expect($('div.alert-success > div').getText()).toEqual('App successful created');
  });

  it('Update app', function() {
    browser.get('#/app');

    var EC = protractor.ExpectedConditions;

    element.all(by.css('div.fusio-options a:nth-child(1)')).first().click();

    browser.wait(EC.visibilityOf($('div.modal-body')), 5000);

    expect(element(by.model('app.status')).getAttribute('value')).toEqual('0');
    expect(element(by.model('app.name')).getAttribute('value')).toEqual('test-app');
    expect(element(by.model('app.url')).getAttribute('value')).toEqual('http://foo.com');

    $('button.btn-primary').click();

    browser.wait(EC.visibilityOf($('div.alert-success')), 5000);

    expect($('div.alert-success > div').getText()).toEqual('App successful updated');
  });

  it('Delete app', function() {
    browser.get('#/app');

    var EC = protractor.ExpectedConditions;

    element.all(by.css('div.fusio-options a:nth-child(2)')).first().click();

    browser.wait(EC.visibilityOf($('div.modal-body')), 5000);

    expect(element(by.model('app.name')).getAttribute('value')).toEqual('test-app');

    $('button.btn-primary').click();

    browser.wait(EC.visibilityOf($('div.alert-success')), 5000);

    expect($('div.alert-success > div').getText()).toEqual('App successful deleted');
  });

});
