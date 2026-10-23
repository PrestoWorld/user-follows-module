import React from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Layout, Menu, Breadcrumb, Card, Input, Button, Space, Avatar, Tag, InputNumber, Select, message, Form, List } from 'antd';
import { UserGroup, Plus, Check, LoadingOutlined } from '@ant-design/icons';

const { Header, Content, Sider } = Layout;

const API = '/api/headless/user-follows';

const UserFollowsScreen: React.FC = () => {
  const [activeTab, setActiveTab] = React.useState<'following' | 'followers'>('following');
  const [targetUser, setTargetUser] = React.useState('');
  const queryClient = useQueryClient();

  const { data: follows = [], isLoading } = useQuery({
    queryKey: ['user-follows', activeTab, targetUser],
    queryFn: async () => {
      const res = await fetch(`${API}/user_follows?user_id=${encodeURIComponent(targetUser || 'user_1')}&direction=${activeTab}`);
      if (!res.ok) throw new Error('Failed to load follows');
      return res.json();
    },
    enabled: targetUser !== '',
  });

  const follow = useMutation({
    mutationFn: async (followingId: string) => {
      const res = await fetch(`${API}/user_follows`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ follower_id: 'user_1', following_id: followingId }),
      });
      if (!res.ok) throw new Error('Failed to follow');
      return res.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user-follows'] });
      message.success('Đã theo dõi');
    },
  });

  const unfollow = useMutation({
    mutationFn: async (followId: string) => {
      const res = await fetch(`${API}/user_follows/${followId}`, { method: 'DELETE' });
      if (!res.ok) throw new Error('Failed to unfollow');
      return res.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user-follows'] });
      message.success('Đã hủy theo dõi');
    },
  });

  return (
    <Layout style={{ minHeight: '100vh' }}>
      <Sider width={220} className="site-layout-background">
        <div style={{ padding: 24, textAlign: 'center' }}>
          <h2>User Follows</h2>
        </div>
        <Menu mode="inline" defaultSelectedKeys={['1']} style={{ height: '100%', borderRight: 0 }}>
          <Menu.Item key="following" icon={<UserGroup />} onClick={() => setActiveTab('following')}>
            Đang theo dõi
          </Menu.Item>
          <Menu.Item key="followers" icon={<UserGroup />} onClick={() => setActiveTab('followers')}>
            Người theo dõi
          </Menu.Item>
        </Menu>
      </Sider>
      <Content style={{ padding: '0 24px 24px', minHeight: 280 }}>
        <Breadcrumb style={{ margin: '16px 0' }}>
          <Breadcrumb.Item>Trang chủ</Breadcrumb.Item>
          <Breadcrumb.Item>User Follows</Breadcrumb.Item>
        </Breadcrumb>

        <Card style={{ maxWidth: 600, marginBottom: 24 }}>
          <Form layout="vertical" onFinish={(v) => setTargetUser(v.user_id)}>
            <Form.Item label="User ID" name="user_id" initialValue="user_1" rules={[{ required: true }]}>
              <Input placeholder="Nhập User ID" />
            </Form.Item>
            <Form.Item>
              <Button type="primary" htmlType="submit">Xem quan hệ</Button>
            </Form.Item>
          </Form>
        </Card>

        {targetUser === '' ? (
          <div style={{ textAlign: 'center', padding: 40, color: '#999' }}>
            Vui lòng nhập User ID để xem quan hệ follow
          </div>
        ) : (
          <Space direction="vertical" style={{ gap: 16 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <h3>{activeTab === 'following' ? 'Đang theo dõi' : 'Người theo dõi'} của {targetUser}</h3>
              <Tag color={activeTab === 'following' ? 'blue' : 'green'}>
                {activeTab === 'following' ? 'Following' : 'Followers'}
              </Tag>
            </div>

            {isLoading ? (
              <div style={{ textAlign: 'center', padding: 40 }}>
                <LoadingOutlined />
              </div>
            ) : follows.length === 0 ? (
              <div style={{ textAlign: 'center', padding: 40, color: '#999' }}>
                Chưa có quan hệ nào
              </div>
            ) : (
              <List
                itemLayout="horizontal"
                dataSource={follows}
                renderItem={(item: any) => (
                  <List.Item
                    actions={[
                      activeTab === 'following' ? (
                        <Button
                          type="danger"
                          size="small"
                          onClick={() => unfollow.mutate(item.external_id || `${item.follower_id}_${item.following_id}`)}
                        >
                          Hủy theo dõi
                        </Button>
                      ) : (
                        <Button
                          type="primary"
                          size="small"
                          onClick={() => follow.mutate(item.following_id || item.user_id)}
                        >
                          Theo dõi
                        </Button>
                      ),
                    ]}
                  >
                    <List.Item.Meta
                      avatar={<Avatar src={undefined} size={40} />}
                      title={item.following_id || item.follower_id || item.user_id || 'Người dùng'}
                      description={`Quan hệ: ${activeTab === 'following' ? 'following' : 'follower'}`}
                    />
                  </List.Item>
                )}
              />
            )}
          </Space>
        )}
      </Content>
    </Layout>
  );
};

export default UserFollowsScreen;
